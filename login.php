<!DOCTYPE html>
<html lang="pl">
<head><title>Logowanie Admin</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-800 flex items-center justify-center h-screen">
    <form id="login-form" class="bg-white p-8 rounded shadow-lg w-80">
        <h2 class="text-xl font-bold mb-4 text-center">Panel Admina</h2>
        <input type="text" name="username" placeholder="Login" class="border p-2 w-full mb-2 rounded">
        <input type="password" name="password" placeholder="Hasło" class="border p-2 w-full mb-4 rounded">
        <button type="submit" class="bg-blue-600 text-white w-full p-2 rounded">Zaloguj</button>
        <p id="err" class="text-red-500 text-sm mt-2 hidden"></p>
    </form>
    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const res = await fetch('api.php?action=login', { method:'POST', body:fd });
            const d = await res.json();
            if(d.success) window.location.href = 'admin.php';
            else { document.getElementById('err').innerText = d.message; document.getElementById('err').classList.remove('hidden'); }
        });
    </script>
</body>
</html>