<?php
$type = $_GET['type'] ?? 'clothes';
$titles = [
    'car' => 'Umowa Sprzedaży Pojazdu',
    'clothes' => 'Umowa Sprzedaży Ubrań',
    'electronics' => 'Umowa Sprzedaży Elektroniki'
];
$title = $titles[$type] ?? 'Umowa';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 py-10">

    <div class="fixed top-0 left-0 w-full bg-white shadow-sm z-10 py-3 text-center">
        <a href="index.php" class="inline-flex items-center text-gray-600 hover:text-blue-600 font-medium">
            &larr; Powrót do listy umów
        </a>
    </div>

    <div id="success-message" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg shadow-xl text-center max-w-md">
            <div class="text-green-500 text-5xl mb-4">&#10003;</div>
            <h2 class="text-2xl font-bold mb-2">Dziękujemy!</h2>
            <p class="text-gray-600 mb-6">Umowa została podpisana i trafiła do akceptacji. Otrzymasz powiadomienie mailowe o zmianie statusu.</p>
            <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Wróć na start</a>
        </div>
    </div>

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg mt-10">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-900"><?= $title ?></h2>
        
        <form id="uks-form" class="space-y-4">
            <input type="hidden" name="type" value="<?= $type ?>">
            <input type="hidden" name="form_load_time" value="<?= time() ?>">
            <input type="text" name="hp_website" class="hidden">
            <input type="text" name="hp_extra" class="hidden" autocomplete="off" autocomplete="off">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data</label>
                    <input type="date" name="date" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dane Sprzedawcy</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="seller_firstname" placeholder="Imię" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                        <input type="text" name="seller_lastname" placeholder="Nazwisko" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <input type="text" name="street" placeholder="Ulica i nr" class="w-full border border-gray-300 p-2 rounded" required>
                <input type="text" name="city" placeholder="Miasto" class="w-full border border-gray-300 p-2 rounded" required>
                <input type="text" name="postal_code" placeholder="Kod pocztowy" class="w-full border border-gray-300 p-2 rounded" required>
            </div>

            <input type="email" name="email" placeholder="Twój Email (do powiadomień)" class="w-full border border-gray-300 p-2 rounded" required>

            <div class="bg-gray-50 p-4 rounded border">
                <label class="block text-sm font-medium mb-2">Metoda płatności</label>
                <select name="payment_method" id="payment-method" class="w-full border p-2 rounded" onchange="togglePayment()">
                    <option value="transfer">Przelew</option>
                    <option value="blik">BLIK</option>
                    <option value="cash">Gotówka</option>
                </select>
                <div id="account-box" class="mt-2">
                    <input type="text" name="account_number" placeholder="Numer konta" class="w-full border p-2 rounded">
                </div>
                <div id="phone-box" class="mt-2 hidden">
                    <input type="text" name="phone_number" placeholder="Nr telefonu BLIK" class="w-full border p-2 rounded">
                </div>
            </div>

            <div class="border-t pt-4">
                <h3 class="font-bold text-lg mb-2">Przedmiot umowy</h3>
                <div id="product-container">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2 bg-blue-50 p-3 rounded">
                        <input type="text" name="item_name[]" placeholder="Nazwa / Marka" class="border p-2 rounded w-full" required>
                        <input type="number" step="0.01" name="item_price[]" placeholder="Kwota (PLN)" class="border p-2 rounded w-full" required>
                        
                        <?php if($type == 'car'): ?>
                            <input type="text" name="item_extra1[]" placeholder="Przebieg" class="border p-2 rounded w-full">
                            <input type="text" name="item_extra2[]" placeholder="VIN / Rocznik" class="border p-2 rounded w-full">
                        <?php elseif($type == 'clothes'): ?>
                            <input type="text" name="item_extra1[]" placeholder="Rozmiar (np. XL)" class="border p-2 rounded w-full">
                            <input type="hidden" name="item_extra2[]" value="">
                        <?php else: ?>
                             <input type="text" name="item_extra1[]" placeholder="Numer Seryjny (S/N)" class="border p-2 rounded w-full">
                             <input type="text" name="item_extra2[]" placeholder="Model" class="border p-2 rounded w-full">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium mb-1">Podpis sprzedającego</label>
                <div class="border border-gray-400 bg-white rounded touch-none">
                    <canvas id="signatureCanvas" width="500" height="150" class="w-full"></canvas>
                </div>
                <input type="hidden" name="signature_image" id="signature_image">
                <button type="button" onclick="clearCanvas()" class="text-red-500 text-xs mt-1 uppercase font-bold">Wyczyść podpis</button>
            </div>

            <div class="flex items-center mt-4">
                <input type="checkbox" id="agree" required class="w-4 h-4 text-blue-600 rounded">
                <label for="agree" class="ml-2 text-sm text-gray-700">Oświadczam, że dane są zgodne z prawdą.</label>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-bold hover:bg-blue-700 shadow-lg mt-4 transition">
                PODPISZ I WYŚLIJ
            </button>
        </form>
    </div>

    <script>
        function togglePayment() {
            const val = document.getElementById('payment-method').value;
            document.getElementById('account-box').classList.toggle('hidden', val !== 'transfer');
            document.getElementById('phone-box').classList.toggle('hidden', val !== 'blik');
        }

        // Canvas Logic
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let drawing = false;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            // Obsługa dotyku i myszy
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: clientX - rect.left, y: clientY - rect.top };
        }

        ['mousedown', 'touchstart'].forEach(ev => canvas.addEventListener(ev, (e) => {
            e.preventDefault(); drawing = true; ctx.beginPath();
            const pos = getPos(e); ctx.moveTo(pos.x, pos.y);
        }));

        ['mousemove', 'touchmove'].forEach(ev => canvas.addEventListener(ev, (e) => {
            if(!drawing) return; e.preventDefault();
            const pos = getPos(e);
            ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.lineTo(pos.x, pos.y); ctx.stroke();
        }));

        ['mouseup', 'touchend'].forEach(ev => canvas.addEventListener(ev, () => drawing = false));

        function clearCanvas() { ctx.clearRect(0,0,canvas.width,canvas.height); }

        // Submit Logic
        document.getElementById('uks-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Zapisz podpis
            const blank = document.createElement('canvas');
            blank.width = canvas.width; blank.height = canvas.height;
            if(canvas.toDataURL() === blank.toDataURL()) {
                alert("Proszę złożyć podpis!"); return;
            }
            document.getElementById('signature_image').value = canvas.toDataURL();

            const formData = new FormData(e.target);
            try {
                const res = await fetch('api.php?action=create', { method: 'POST', body: formData });
                const data = await res.json();
                if(data.success) {
                    document.getElementById('success-message').classList.remove('hidden');
                } else {
                    alert('Błąd: ' + data.message);
                }
            } catch(err) { alert('Wystąpił błąd komunikacji.'); }
        });
    </script>
</body>
</html>