<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Contract {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function generateId() {
        $suffix = '-' . date('m-Y');
        $stmt = $this->db->prepare("SELECT contract_id FROM contracts WHERE contract_id LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(["____$suffix"]);
        $last = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$last) return "0001" . $suffix;
        $num = intval(substr($last['contract_id'], 0, 4));
        return str_pad($num + 1, 4, '0', STR_PAD_LEFT) . $suffix;
    }

    public function create($data) {
        // Zabezpieczenie: Honeypot
        if (!empty($data['hp_website'])) {
            return ['success' => false, 'message' => 'Bot detected (honeypot).'];
        }
        // Zabezpieczenie: Czas wypełniania (min 15s)
        $loadTime = isset($data['form_load_time']) ? intval($data['form_load_time']) : 0;
        if (time() - $loadTime < 15) {
             return ['success' => false, 'message' => 'Formularz wypełniony za szybko. Podejrzenie bota.'];
        }

        $id = $this->generateId();
        
        // Przygotowanie JSON z produktami zależnie od typu
        $products = [];
        // Logika pobierania tablic z formularza (uproszczona)
        $names = $data['item_name'] ?? [];
        $prices = $data['item_price'] ?? [];
        $extras1 = $data['item_extra1'] ?? []; // Rozmiar / Przebieg / SN
        $extras2 = $data['item_extra2'] ?? []; // VIN / Model / Marka
        
        for($i=0; $i<count($names); $i++){
            $products[] = [
                'name' => $names[$i],
                'price' => $prices[$i],
                'extra1' => $extras1[$i] ?? '',
                'extra2' => $extras2[$i] ?? ''
            ];
        }

        $total = array_sum($prices);
        
        $sql = "INSERT INTO contracts (contract_id, type, date, seller_name, street, city, postal_code, email, products, total_amount, signature_seller, account_number, phone_number, payment_method, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            $id, $data['type'], $data['date'], $data['seller_name'], $data['street'], $data['city'], 
            $data['postal_code'], $data['email'], json_encode($products), $total, $data['signature_image'],
            $data['account_number'], $data['phone_number'], $data['payment_method']
        ]);
        
        return ['success' => $res, 'id' => $id];
    }

    public function getPending() {
        $stmt = $this->db->query("SELECT * FROM contracts WHERE status = 'pending' ORDER BY created_at ASC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAccepted($search = '', $filter = '') {
        $sql = "SELECT * FROM contracts WHERE status = 'accepted'";
        if($search) {
            $sql .= " AND (contract_id LIKE :s OR seller_name LIKE :s OR email LIKE :s)";
        }
        // Filtracja po typie (jeśli potrzebna)
        if($filter && $filter !== 'all') {
             $sql .= " AND type = :f";
        }
        
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        if($search) $stmt->bindValue(':s', "%$search%");
        if($filter && $filter !== 'all') $stmt->bindValue(':f', $filter);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function toggleWarranty($id) {
        // Najpierw sprawdzamy aktualny stan
        $stmt = $this->db->prepare("SELECT is_warranty FROM contracts WHERE contract_id = ?");
        $stmt->execute([$id]);
        $curr = $stmt->fetchColumn();
        
        $newState = $curr ? 0 : 1;
        $upd = $this->db->prepare("UPDATE contracts SET is_warranty = ? WHERE contract_id = ?");
        return $upd->execute([$newState, $id]);
    }

    public function reject($id, $reason) {
        $stmt = $this->db->prepare("UPDATE contracts SET status = 'rejected', rejection_reason = ? WHERE contract_id = ?");
        $res = $stmt->execute([$reason, $id]);
        if($res) {
            // Tutaj logika wysyłania maila o odrzuceniu
            $this->sendEmail($id, 'rejected', $reason);
        }
        return $res;
    }

    public function accept($id) {
        // Ścieżka do podpisu admina (plik statyczny na serwerze)
        // Zakładamy, że plik admin_signature.png jest w folderze public/img/
        // Dla uproszczenia w PDF użyjemy stałej ścieżki, tutaj tylko zmieniamy status
        
        $stmt = $this->db->prepare("UPDATE contracts SET status = 'accepted' WHERE contract_id = ?");
        $res = $stmt->execute([$id]);
        
        if($res) {
            // Generuj finalny PDF z podpisem admina i wyślij maila
            $pdfContent = $this->generatePDF($id, true, 'S'); // S = String return
            $this->sendEmail($id, 'accepted', '', $pdfContent);
        }
        return $res;
    }

    // $mode: 'D' - download, 'I' - inline, 'S' - string (dla emaila)
    // $withAdminSig: czy dodać podpis admina
    public function generatePDF($id, $withAdminSig = false, $mode = 'D') {
        $stmt = $this->db->prepare("SELECT * FROM contracts WHERE contract_id = ?");
        $stmt->execute([$id]);
        $c = $stmt->fetch(PDO::FETCH_OBJ);
        if(!$c) die("Nie znaleziono umowy");

        $products = json_decode($c->products, true);
        
        // Budowanie tabeli produktów zależnie od typu
        $prodHtml = '<table style="width:100%; border-collapse: collapse; margin-top:10px;">
                        <tr style="background:#eee;">
                            <th style="border:1px solid #ddd; padding:5px;">Nazwa</th>
                            <th style="border:1px solid #ddd; padding:5px;">';
        
        if($c->type == 'car') $prodHtml .= 'VIN / Rocznik';
        elseif($c->type == 'clothes') $prodHtml .= 'Rozmiar';
        elseif($c->type == 'electronics') $prodHtml .= 'S/N';
        
        $prodHtml .= '</th><th style="border:1px solid #ddd; padding:5px;">Detale</th>
                      <th style="border:1px solid #ddd; padding:5px;">Cena</th></tr>';

        foreach($products as $p) {
            $prodHtml .= '<tr>
                <td style="border:1px solid #ddd; padding:5px;">'.$p['name'].'</td>
                <td style="border:1px solid #ddd; padding:5px;">'.$p['extra1'].'</td>
                <td style="border:1px solid #ddd; padding:5px;">'.$p['extra2'].'</td>
                <td style="border:1px solid #ddd; padding:5px;">'.number_format($p['price'], 2).' PLN</td>
            </tr>';
        }
        $prodHtml .= '</table>';

        // Treść prawna (skrócona na potrzeby skryptu, wstaw tu pełną z karty)
        $legalText = '
        <h3>§1 Przedmiot Umowy</h3>
        <p>Sprzedający oświadcza, że jest właścicielem przedmiotów wymienionych powyżej.</p>
        <h3>§2 Cena i Płatność</h3>
        <p>Strony ustaliły łączną cenę na kwotę: <b>'.number_format($c->total_amount, 2).' PLN</b>.</p>
        <p>Metoda płatności: '.$c->payment_method.'. ' . ($c->account_number ? "Nr konta: $c->account_number" : "") . '</p>
        <h3>§3 Postanowienia Końcowe</h3>
        <p>W sprawach nieuregulowanych niniejszą umową mają zastosowanie przepisy Kodeksu Cywilnego.</p>
        ';

        // Podpisy
        $adminSigHtml = '';
        if($withAdminSig) {
            // W realnym projekcie: base64 lub ścieżka. Tutaj symulujemy placeholderm.
            // Upewnij się, że masz plik public/img/admin_sign.png!
            $path = __DIR__ . '/../public/img/admin_sign.png';
            if(file_exists($path)) {
                 $adminSigHtml = '<img src="'.$path.'" style="max-height:80px;">';
            } else {
                 $adminSigHtml = '<div style="color:red">[Brak pliku podpisu admina]</div>';
            }
        }

        $html = '
        <html>
        <head><style>body { font-family: sans-serif; font-size: 12px; }</style></head>
        <body>
            <div style="text-align:center; margin-bottom:20px;">
                <h1>UMOWA KUPNA-SPRZEDAŻY</h1>
                <h2>NR: '.$c->contract_id.'</h2>
                <p>Data zawarcia: '.$c->date.'</p>
            </div>
            
            <table style="width:100%; margin-bottom:20px;">
                <tr>
                    <td style="width:45%; vertical-align:top;">
                        <strong>SPRZEDAJĄCY:</strong><br>
                        '.$c->seller_name.'<br>
                        '.$c->street.'<br>
                        '.$c->postal_code.' '.$c->city.'<br>
                        Email: '.$c->email.'
                    </td>
                    <td style="width:10%;"></td>
                    <td style="width:45%; vertical-align:top;">
                        <strong>KUPUJĄCY (Administrator):</strong><br>
                        SampleStore Sp. z o.o.<br>
                        ul. Przykładowa 1<br>
                        00-001 Warszawa<br>
                        NIP: 123-456-78-90
                    </td>
                </tr>
            </table>

            '.$prodHtml.'
            
            '.$legalText.'
            
            <table style="width:100%; margin-top:50px;">
                <tr>
                    <td style="text-align:center; width:50%;">
                        <img src="'.$c->signature_seller.'" style="max-height:80px;"><br>
                        _______________________<br>Podpis Sprzedającego
                    </td>
                    <td style="text-align:center; width:50%;">
                        '.$adminSigHtml.'<br>
                        _______________________<br>Podpis Kupującego
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        
        if($mode == 'S') return $mpdf->Output('', 'S');
        $mpdf->Output('Umowa_'.$id.'.pdf', $mode);
    }

    private function sendEmail($id, $type, $reason = '', $pdfContent = null) {
        $mail = new PHPMailer(true);

        try {
            // Konfiguracja serwera
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // Nadawca
            $mail->setFrom(SMTP_USER, 'System UKS');

            // Odbiorca (Klient)
            $stmt = $this->db->prepare("SELECT email FROM contracts WHERE contract_id = ?");
            $stmt->execute([$id]);
            $userEmail = $stmt->fetchColumn();

            $mail->addAddress($userEmail);     // Wyślij do klienta
            $mail->addBCC(ADMIN_EMAIL);        // Ukryta kopia do Admina

            // Treść
            $mail->isHTML(true);
            
            if($type == 'accepted') {
                $mail->Subject = "Akceptacja umowy nr $id";
                $mail->Body    = "
                    <h2>Dzień dobry!</h2>
                    <p>Twoja umowa o numerze <strong>$id</strong> została pomyślnie zweryfikowana i zaakceptowana przez administratora.</p>
                    <p>W załączniku znajduje się finalny dokument PDF z obustronnymi podpisami.</p>
                    <br>
                    <p>Pozdrawiamy,<br>Zespół SampleStore</p>
                ";
                // Załącz PDF z pamięci (string)
                if($pdfContent) {
                    $mail->addStringAttachment($pdfContent, "Umowa_$id.pdf");
                }
            } elseif($type == 'rejected') {
                $mail->Subject = "Odrzucenie umowy nr $id";
                $mail->Body    = "
                    <h2>Witaj.</h2>
                    <p>Niestety, Twoja umowa o numerze <strong>$id</strong> została odrzucona.</p>
                    <p style='color:red;'>Powód odrzucenia: <strong>$reason</strong></p>
                    <p>Prosimy o ponowne wypełnienie formularza z poprawnymi danymi.</p>
                    <br>
                    <p>Pozdrawiamy,<br>Zespół SampleStore</p>
                ";
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Logowanie błędu do pliku, jeśli mail nie wyjdzie
            file_put_contents(__DIR__ . '/../logs/mail_error.txt', "Błąd wysyłania: {$mail->ErrorInfo}\n", FILE_APPEND);
            return false;
        }
    }

    public function bulkDelete($ids) {
        if(empty($ids) || !is_array($ids)) return false;
        // Tworzymy string ze znakami zapytania np. ?,?,?
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM contracts WHERE contract_id IN ($placeholders)");
        return $stmt->execute($ids);
    }
}
?>