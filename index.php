<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wybierz rodzaj umowy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">
    
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Generator Umów Kupna-Sprzedaży</h1>
        <p class="text-gray-600">Wybierz kategorię przedmiotu, aby rozpocząć</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl w-full">
        <a href="form.php?type=car" class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1 cursor-pointer flex flex-col items-center text-center group">
            <div class="bg-blue-100 p-4 rounded-full mb-4 group-hover:bg-blue-600 transition">
                <i class="fa-solid fa-car text-3xl text-blue-600 group-hover:text-white transition"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Pojazd</h2>
            <p class="text-sm text-gray-500 mt-2">Umowa dla samochodów, motocykli i innych pojazdów.</p>
        </a>

        <a href="form.php?type=clothes" class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1 cursor-pointer flex flex-col items-center text-center group">
            <div class="bg-pink-100 p-4 rounded-full mb-4 group-hover:bg-pink-600 transition">
                <i class="fa-solid fa-shirt text-3xl text-pink-600 group-hover:text-white transition"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Ubrania</h2>
            <p class="text-sm text-gray-500 mt-2">Umowa dla odzieży, obuwia i akcesoriów.</p>
        </a>

        <a href="form.php?type=electronics" class="bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1 cursor-pointer flex flex-col items-center text-center group">
            <div class="bg-purple-100 p-4 rounded-full mb-4 group-hover:bg-purple-600 transition">
                <i class="fa-solid fa-laptop text-3xl text-purple-600 group-hover:text-white transition"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Elektronika</h2>
            <p class="text-sm text-gray-500 mt-2">Telefony, laptopy, konsole i sprzęt RTV/AGD.</p>
        </a>
    </div>

</body>
</html>