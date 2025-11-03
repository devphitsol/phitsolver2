<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - PHITSOL</title>
    <link href="/assets/css/DesignSystem.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
        <header class="app-header">
            <!-- HEADER CONTENT -->
            <?= $headerContent ?? '' ?>
        </header>
        <nav class="app-sidebar">
            <!-- SIDEBAR CONTENT -->
            <?= $sidebarContent ?? '' ?>
        </nav>
        <main class="app-main">
            <!-- MAIN CONTENT -->
            <?= $mainContent ?? '' ?>
        </main>
    </div>
</body>
</html> 