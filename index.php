<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /Login");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="asset/fingerprint.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="/style.css?v=1.4">
    <script src="script.js"></script>

    <title>Libraprint</title>

</head>
<body class="bg-gradient-to-b from-[#304475] to-[#0c0c0c] bg-fixed overflow-hidden min-h-screen">
    <header class="bg-gray-900 text-white sticky top-0 z-10">
        <section class="max-w-[100rem] mx-auto p-3 lg:p-2 flex justify-between items-center">
            <div class="flex items-center mx-4 space-x-6">
                <div>
                    <button id="menu" class="text-[30px] focus:outline-none cursor-pointer hover:opacity-60 active:opacity-40 transition-opacity duration-200 p-2">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div id="brand" class="flex items-center space-x-1.5">
                    <i class="fas fa-fingerprint text-white text-[30px]"></i>
                    <a class="text-2xl font-serif" href="https://libraprintlucena.com">Libraprint</a>
                </div>
            </div>
            <div class="flex items-center mx-4 space-x-6">
                <div class="hidden sm:block">
                    <nav>
                        <ul class="md:flex md:flex-col lg:flex-row md:text-sm space-x-6">
                            <li><a href="https://libraprintlucena.com" class="font-bold hover:opacity-60 transition-opacity duration-200">Home</a></li>
                            <li><a href="/AboutUs" class="hover:opacity-60 transition-opacity duration-200">About Us</a></li>
                            <li><a href="/ContactUs" class="hover:opacity-60 transition-opacity duration-200">Contact Us</a></li>
                        </ul>
                    </nav>
                </div>
                <div id="mobile-search" class="relative hidden sm:block">
                        <input id="search-input" type="search"
                            placeholder="Search..."
                            class="pl-3 pr-8 py-1 rounded-full w-full bg-white text-black focus:outline-none appearance-none border-none outline-none" />
                        <i class="fas fa-search absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <div>
                    <button id="search-toggle" class="cursor-pointer hover:opacity-60 active:opacity-40 block p-2 sm:hidden">
                        <i class="fas fa-search text-white text-2xl"></i>
                    </button>
                </div>
            </div>

        </section>
    </header>

    <div class="flex">
        <div id ="side-menu" class="hidden flex-col bg-[#F4F4F4] w-fit z-10">
            <nav class="flex flex-col bg-[#F4F4F4] h-[calc(100vh-84px)] justify-between">
                <ul>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="asset/profile.png">Profile</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="asset/book_r.png">Book Reservation</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="asset/book_b.png">Book Borrowing</a></li>
                    <li><a class=" text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href=""><img class="w-8 m-2" src="asset/setting.png">Settings</a></li>
                    <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/AboutUs"><img class="w-8 m-2" src="asset/about_us.png">About Us</a></li>
                    <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/ContactUs"><img class="w-8 m-2" src="asset/contact_us.png">Contact Us</a></li>
                    <li><a class="sm:hidden  text-black text-2xl hover:bg-slate-200 active:bg-slate-300 w-full px-5 py-4 flex items-center" href="/logout.php"><img class="w-8 m-2" src="asset/contact_us.png"></a></li>
                </ul>
            </nav>
        </div>
     
        <div class="absolute justify-center items-center flex w-full h-[calc(100vh-94px)]">
            <section class=" relative w-full h-full flex justify-center items-center px-10 sm:px-32 gap-8 flex-col sm:flex-row">
                <div class="flex flex-col items-center w-fit">
                    <img draggable="false" class=" pointer-events-nonew-full lg:w-2/3" src="asset/Welcome.png" alt="Welcome to Libraprint">
                    <a class="block bg-cyan-700 rounded-full px-4 py-2 mt-4 text-white hover:bg-cyan-800 active:bg-cyan-900" href="">Reserve Books Now!</a>
                </div>
                <div class="hidden lg:block">
                    <img draggable="false" class="w-full h-[800px] object-contain pointer-events-none" src="asset/books.png" alt="">
                </div>
            </section>
        </div>
    </div>
</body>
</html>
