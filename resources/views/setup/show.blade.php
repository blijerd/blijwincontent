<!doctype html>
<html lang="nl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex,nofollow">
        <title>Setup - Blijwin Content</title>
        @vite('resources/css/app.css')
    </head>
    <body>
        <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-10">
            <section class="w-full max-w-lg rounded-lg border border-white/20 bg-white px-6 py-7 text-[#2b1250] shadow-2xl sm:px-8">
                <p class="text-sm font-bold uppercase tracking-wide text-[#6d35da]">Blijwin Content CMS</p>
                <h1 class="mt-2 font-['Grandstander'] text-3xl font-bold text-[#22063e]">Eerste beheerder aanmaken</h1>
                <p class="mt-3 text-base text-[#715d92]">
                    Maak het eerste admin-account aan. Daarna wordt deze setup automatisch afgesloten.
                </p>

                <form method="POST" action="{{ route('setup.store') }}" class="mt-7 space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-bold text-[#2b1250]">Naam</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            autocomplete="name"
                            autofocus
                            required
                            class="mt-2 block w-full rounded-md border border-[#d8ccef] bg-white px-3 py-2.5 text-[#2b1250] outline-none transition focus:border-[#6d35da] focus:ring-4 focus:ring-[#6d35da]/15"
                        >
                        @error('name')
                            <p class="mt-1 text-sm font-semibold text-[#c51b5f]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-[#2b1250]">E-mailadres</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            class="mt-2 block w-full rounded-md border border-[#d8ccef] bg-white px-3 py-2.5 text-[#2b1250] outline-none transition focus:border-[#6d35da] focus:ring-4 focus:ring-[#6d35da]/15"
                        >
                        @error('email')
                            <p class="mt-1 text-sm font-semibold text-[#c51b5f]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-[#2b1250]">Wachtwoord</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-2 block w-full rounded-md border border-[#d8ccef] bg-white px-3 py-2.5 text-[#2b1250] outline-none transition focus:border-[#6d35da] focus:ring-4 focus:ring-[#6d35da]/15"
                        >
                        @error('password')
                            <p class="mt-1 text-sm font-semibold text-[#c51b5f]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-[#2b1250]">Wachtwoord herhalen</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-2 block w-full rounded-md border border-[#d8ccef] bg-white px-3 py-2.5 text-[#2b1250] outline-none transition focus:border-[#6d35da] focus:ring-4 focus:ring-[#6d35da]/15"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-md bg-[#6d35da] px-4 py-3 text-base font-extrabold text-white shadow-lg shadow-[#6d35da]/25 transition hover:bg-[#5727b6] focus:outline-none focus:ring-4 focus:ring-[#6d35da]/25"
                    >
                        Account aanmaken
                    </button>
                </form>
            </section>
        </main>
    </body>
</html>
