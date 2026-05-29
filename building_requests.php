<?php
require 'includes/auth.php';
$page_title = 'Select Building';

// Curated building list — [image file => display name]
$buildings = [
    'building_images/BA bulding.jpeg'                  => 'BA Building',
    'building_images/BB Building.jpeg'                 => 'BB Building',
    'building_images/STC Building.jpeg'                => 'STC Building',
    'building_images/ITC Bulding.jpeg'                 => 'ITC Building',
    'building_images/SA Bulding.jpeg'                  => 'SA Building',
    'building_images/SB Building.jpeg'                 => 'SB Building',
    'building_images/Sports Bulding.jpeg'              => 'Sports Complex',
    'building_images/Classroom.jpeg'                   => 'Classrooms ',
    'building_images/compuer lab.jpeg'                 => 'Computer Labs',
    'building_images/Campus outdoors.jpeg'             => 'Campus Grounds',
];
?>
<?php require 'includes/header.php'; ?>

<div class="container py-4">

    <div class="page-header">
        <i class="bi bi-buildings"></i>
        <div>
            <h5>Select a Building</h5>
            <small class="text-muted">Click a building to begin your maintenance request</small>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">

        <?php foreach ($buildings as $img_path => $building):
            $encoded = urlencode($building);
        ?>
            <div class="col">
                <a href="create_request.php?building=<?= $encoded ?>"
                   class="building-card">
                    <img src="<?= htmlspecialchars($img_path) ?>"
                         alt="<?= htmlspecialchars($building) ?>"
                         loading="lazy">
                    <div class="overlay">
                        <i class="bi bi-arrow-right-circle-fill"></i>
                        <?= htmlspecialchars($building) ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

        <!-- Other / General Location Card -->
        <div class="col">
            <a href="create_request.php?building=Other+%2F+General+Location"
               class="building-card">
                <div style="width:100%;height:210px;background:linear-gradient(135deg,#970000,#5c0000);
                            display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-geo-alt-fill text-white" style="font-size:4rem;opacity:0.4;"></i>
                </div>
                <div class="overlay">
                    <i class="bi bi-arrow-right-circle-fill"></i>
                    Other / General Location
                </div>
            </a>
        </div>

    </div>
</div>

<?php require 'includes/footer.php'; ?>
