<?php
$page = basename($_SERVER['PHP_SELF']);
?>

<nav class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50
            flex items-center justify-around
            w-[90%] max-w-md h-16
            bg-white rounded-3xl shadow-xl">

    <a href="/PROJET_TUTORE/DashboardGestionnaire.php" class="<?= $page == 'DashboardGestionnaire.php'
            ? 'text-white bg-violet-600 -translate-y-3 shadow-lg shadow-violet-300'
            : 'text-gray-400'
?>
        flex items-center justify-center w-12 h-12 rounded-full transition-all duration-300">

        <i class="fa-solid fa-house text-xl"></i>
    </a>

    <a href="/PROJET_TUTORE/action/mesActivites.php" class="<?= $page == 'mesActivites.php'
    ? 'text-white bg-violet-600 -translate-y-3 shadow-lg shadow-violet-300'
    : 'text-gray-400'
?>
        flex items-center justify-center w-12 h-12 rounded-full transition-all duration-300">

        <i class="fa-solid fa-calendar-days text-xl"></i>
    </a>

    <a href="/PROJET_TUTORE/action/formCreationActivite.php" class="<?= $page == 'formCreationActivite.php'
    ? 'bg-violet-700 scale-110'
    : 'bg-gray-600'
?>
        flex items-center justify-center
        w-16 h-16 rounded-full
        text-white text-2xl
        -translate-y-5
        shadow-xl shadow-violet-300
        transition-all duration-300">

        <i class="fa-solid fa-plus"></i>
    </a>

    <a href="/PROJET_TUTORE/action/Affiche_notif.php" class="<?= $page == 'Affiche_notif.php'
    ? 'text-white bg-violet-600 -translate-y-3 shadow-lg shadow-violet-300'
    : 'text-gray-400'
?>
        flex items-center justify-center w-12 h-12 rounded-full transition-all duration-300">

        <i class="fa-solid fa-bell text-xl"></i>
    </a>

    <div class="text-white bg-yellow-600
           -translate-y-3
           shadow-lg
           flex items-center justify-center
           w-12 h-12 rounded-full
           font-bold
           cursor-default">

        <?= strtoupper(substr($_SESSION['prenom'], 0, 1)) ?>

    </div>
</nav>