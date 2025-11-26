<?php
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Contract.php';

if(!isset($_SESSION['admin_logged'])) { header('Location: login.php'); exit; }

$contract = new Contract();
$pending = $contract->getPending();
// Filtrowanie zaakceptowanych
$search = $_GET['s'] ?? '';
$filterType = $_GET['f'] ?? 'all';
$accepted = $contract->getAccepted($search, $filterType);

$reasons = ['Brak czytelnego podpisu', 'Błędne dane kwotowe', 'Niezgodność towaru z opisem', 'Inne'];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Admina UKS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>.tab-active { border-bottom: 2px solid blue; color: blue; }</style>
</head>
<body class="bg-gray-100">

    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <div class="font-bold text-xl">UKS Admin Panel</div>
        <div>
            <span class="mr-4 text-gray-600">Zalogowany jako Admin</span>
            <a href="api.php?action=logout" class="text-red-500 hover:underline">Wyloguj</a>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        
        <div class="flex mb-6 border-b">
            <button onclick="switchTab('pending')" id="btn-pending" class="px-6 py-3 font-medium tab-active">Oczekujące (<?= count($pending) ?>)</button>
            <button onclick="switchTab('accepted')" id="btn-accepted" class="px-6 py-3 font-medium text-gray-500">Zaakceptowane / Archiwum</button>
        </div>

        <div id="tab-pending">
            <?php if(empty($pending)): ?>
                <div class="p-10 text-center text-gray-500 bg-white rounded shadow">Brak nowych umów do akceptacji.</div>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach($pending as $c): ?>
                    <div class="bg-white p-4 rounded shadow flex justify-between items-center">
                        <div>
                            <div class="font-bold text-lg"><?= $c->contract_id ?> <span class="text-xs bg-gray-200 px-2 py-1 rounded"><?= $c->type ?></span></div>
                            <div class="text-gray-600"><?= $c->seller_name ?> | <?= number_format($c->total_amount, 2) ?> PLN</div>
                            <div class="text-sm text-gray-400"><?= $c->created_at ?></div>
                        </div>
                        <div class="flex gap-2">
                            <a href="pdf_preview.php?id=<?= $c->contract_id ?>" target="_blank" class="bg-blue-100 text-blue-600 px-4 py-2 rounded hover:bg-blue-200">Podgląd PDF</a>
                            <button onclick="openRejectModal('<?= $c->contract_id ?>')" class="bg-red-100 text-red-600 px-4 py-2 rounded hover:bg-red-200">Odrzuć</button>
                            <button onclick="acceptContract('<?= $c->contract_id ?>')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Akceptuj i Podpisz</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="tab-accepted" class="hidden">
            <form class="mb-4 flex gap-2">
                <input type="text" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Szukaj (ID, nazwisko, email)..." class="border p-2 rounded w-full md:w-1/3">
                <select name="f" class="border p-2 rounded">
                    <option value="all">Wszystkie typy</option>
                    <option value="car" <?= $filterType=='car'?'selected':'' ?>>Pojazdy</option>
                    <option value="clothes" <?= $filterType=='clothes'?'selected':'' ?>>Ubrania</option>
                    <option value="electronics" <?= $filterType=='electronics'?'selected':'' ?>>Elektronika</option>
                </select>
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded">Filtruj</button>
            </form>

            <table class="w-full bg-white shadow rounded overflow-hidden">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Sprzedawca</th>
                        <th class="p-3 text-left">Kwota</th>
                        <th class="p-3 text-left">Data</th>
                        <th class="p-3 text-left">Opcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($accepted as $c): ?>
                    <tr class="border-b hover:bg-gray-50 <?= $c->is_warranty ? 'bg-red-50 text-red-900' : '' ?>">
                        <td class="p-3 font-mono">
                            <?= $c->contract_id ?>
                            <?php if($c->is_warranty): ?><span class="text-xs font-bold text-red-600 ml-2">[RĘKOJMIA]</span><?php endif; ?>
                        </td>
                        <td class="p-3"><?= $c->seller_name ?></td>
                        <td class="p-3"><?= $c->total_amount ?> PLN</td>
                        <td class="p-3"><?= $c->date ?></td>
                        <td class="p-3 flex gap-2">
                            <a href="pdf_preview.php?id=<?= $c->contract_id ?>&final=1" class="text-blue-600 hover:underline">Pobierz PDF</a>
                            <button onclick="toggleWarranty('<?= $c->contract_id ?>')" class="text-orange-600 hover:underline text-sm ml-2">
                                <?= $c->is_warranty ? 'Usuń Rękojmię' : 'Oznacz Rękojmię' ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <div id="reject-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h3 class="font-bold text-lg mb-4">Odrzuć umowę</h3>
            <label class="block mb-2 text-sm">Wybierz powód:</label>
            <select id="reject-reason" class="w-full border p-2 rounded mb-4">
                <?php foreach($reasons as $r): echo "<option>$r</option>"; endforeach; ?>
            </select>
            <div class="flex justify-end gap-2">
                <button onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-4 py-2 text-gray-600">Anuluj</button>
                <button onclick="confirmReject()" class="bg-red-600 text-white px-4 py-2 rounded">Potwierdź</button>
            </div>
        </div>
    </div>

    <script>
        let currentRejectId = null;

        function switchTab(tab) {
            document.getElementById('tab-pending').classList.add('hidden');
            document.getElementById('tab-accepted').classList.add('hidden');
            document.getElementById('btn-pending').classList.remove('tab-active', 'text-gray-500');
            document.getElementById('btn-accepted').classList.remove('tab-active', 'text-gray-500');

            document.getElementById('tab-' + tab).classList.remove('hidden');
            document.getElementById('btn-' + tab).classList.add('tab-active');
            document.getElementById('btn-' + (tab==='pending'?'accepted':'pending')).classList.add('text-gray-500');
        }

        // Ustawienie zakładki po odświeżeniu jeśli są filtry
        <?php if($search || $filterType != 'all') echo "switchTab('accepted');"; ?>

        async function acceptContract(id) {
            if(!confirm('Czy na pewno podpisać i zaakceptować umowę ' + id + '?')) return;
            const fd = new FormData(); fd.append('id', id);
            const res = await fetch('api.php?action=accept', { method:'POST', body:fd });
            location.reload();
        }

        function openRejectModal(id) {
            currentRejectId = id;
            document.getElementById('reject-modal').classList.remove('hidden');
        }

        async function confirmReject() {
            const reason = document.getElementById('reject-reason').value;
            const fd = new FormData(); 
            fd.append('id', currentRejectId);
            fd.append('reason', reason);
            const res = await fetch('api.php?action=reject', { method:'POST', body:fd });
            location.reload();
        }

        async function toggleWarranty(id) {
            const fd = new FormData(); fd.append('id', id);
            await fetch('api.php?action=toggle_warranty', { method:'POST', body:fd });
            location.reload(); // Odśwież, żeby zobaczyć czerwony pasek
        }
    </script>
</body>
</html>