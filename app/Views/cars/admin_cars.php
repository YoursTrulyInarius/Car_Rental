<?php
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="admin-main">
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <p class="admin-kicker mb-1">Fleet Inventory</p>
                <h1 class="fw-bold mb-0">Fleet Management</h1>
            </div>
            
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#carModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle me-2"></i>Add New Car
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase text-muted small fw-bold tracking-wide">Vehicle</th>
                                <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Model</th>
                                <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Type</th>
                                <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Year</th>
                                <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Daily Rate</th>
                                <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Available Stock</th>
                                <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Status</th>
                                <th class="text-end pe-4 py-3 text-uppercase text-muted small fw-bold tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($cars && $cars->num_rows > 0): ?>
                                <?php while($row = $cars->fetch_assoc()): ?>
                                    <?php 
                                        $imgs = getCarImagesList($row['image']); 
                                        $primary_img = !empty($imgs) ? BASE_URL . 'uploads/' . $imgs[0] : null;

                                        $car_id = (int)$row['id'];
                                        $total_stock = (int)$row['quantity'];
                                        $today = date('Y-m-d');
                                        $active_count = $this->carModel->getActiveBookingsCount($car_id, $today);
                                        $available_stock = max(0, $total_stock - $active_count);
                                        
                                        $is_fully_rented = ($available_stock <= 0 || $row['status'] === 'rented');
                                        $real_status = $is_fully_rented ? 'rented' : $row['status'];
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="position-relative d-inline-block" style="cursor: pointer;" onclick='viewGallery(<?php echo htmlspecialchars(json_encode($imgs), ENT_QUOTES, 'UTF-8'); ?>, "<?php echo htmlspecialchars($row['model']); ?>")' title="Click to view all photos">
                                                <?php if($primary_img): ?>
                                                    <img src="<?php echo htmlspecialchars($primary_img); ?>" width="70" height="46" class="rounded-3 shadow-sm object-fit-cover hover-opacity" alt="Car">
                                                    <?php if(count($imgs) > 0): ?>
                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary shadow-sm">
                                                            <i class="bi bi-images me-1"></i><?php echo count($imgs); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="width:70px; height:46px;">
                                                        <i class="bi bi-car-front fs-4"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($row['model']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1 text-capitalize">
                                                <i class="bi bi-gear-wide-connected me-1 text-primary"></i>
                                                <?php echo htmlspecialchars(ucfirst($row['type'] ?? 'Sedan')); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo $row['year']; ?></td>
                                        <td class="fw-bold text-primary">₱<?php echo number_format($row['price_per_day'], 2); ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold fs-6 <?php echo ($available_stock > 0) ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $available_stock; ?> Available
                                                </span>
                                                <small class="text-muted">Total units: <?php echo $total_stock; ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($is_fully_rented): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">
                                                    <i class="bi bi-x-circle-fill me-1"></i>Fully Rented
                                                </span>
                                            <?php elseif ($real_status === 'maintenance'): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2">
                                                    <i class="bi bi-tools me-1"></i>Maintenance
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Available
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-light border rounded-pill px-3 fw-bold me-1 hover-shadow" onclick='editCar(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)'>
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>
                                            <a href="<?php echo BASE_URL; ?>admin/cars.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-danger rounded-pill px-3 fw-bold hover-shadow" onclick="return confirm('Delete this car? This action cannot be undone.')">
                                                <i class="bi bi-trash me-1"></i>Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-5 text-muted">No cars found in fleet. Click "Add New Car" above to add one.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Car Modal -->
<div class="modal fade" id="carModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="<?php echo BASE_URL; ?>admin/cars.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="modalTitle">Add New Car</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="car_id" id="car_id">
                    <input type="hidden" name="current_image" id="current_image">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Brand</label>
                            <input type="text" name="brand" id="brand" class="form-control rounded-3" placeholder="e.g. Toyota" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Car Model</label>
                            <input type="text" name="model" id="model" class="form-control rounded-3" placeholder="e.g. Camry 2.5V" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Plate Number</label>
                            <input type="text" name="plate_number" id="plate_number" class="form-control rounded-3" placeholder="ABC-1234" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Vehicle Type</label>
                            <select name="type" id="type" class="form-select rounded-3">
                                <option value="sedan">Sedan</option>
                                <option value="suv">SUV</option>
                                <option value="truck">Truck / Pickup</option>
                                <option value="van">Van / Minivan</option>
                                <option value="sports">Sports Car</option>
                                <option value="convertible">Convertible</option>
                                <option value="luxury">Luxury</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="status" class="form-select rounded-3">
                                <option value="available">Available</option>
                                <option value="rented">Rented (Fully Booked)</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Year</label>
                            <input type="number" name="year" id="year" class="form-control rounded-3" placeholder="2024" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Price per Day (₱)</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control rounded-3" placeholder="2500.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Total Fleet Stock</label>
                            <input type="number" name="quantity" id="quantity" class="form-control rounded-3" min="1" value="1" required>
                            <small class="text-muted">Total units of this car model in fleet</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="description" class="form-control rounded-3" rows="3" placeholder="Provide vehicle details, features, transmission type, fuel policy, etc."></textarea>
                    </div>

                    <!-- Multi-Image Upload & Preview Section -->
                    <div class="border rounded-3 p-3 bg-light mb-3">
                        <label class="form-label fw-bold d-block mb-2">
                            <i class="bi bi-images me-2 text-primary"></i>Vehicle Gallery Photos (Select up to 4 photos max)
                        </label>

                        <!-- Image Preview Thumbnails Container -->
                        <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mb-3" style="display: none;"></div>

                        <div>
                            <input type="file" name="images[]" id="carImagesInput" multiple class="form-control rounded-3" accept="image/*" onchange="handleImageSelection(this)">
                        </div>
                        <div id="imageCountBadge" class="small text-primary fw-semibold mt-2" style="display: none;"></div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Car to Fleet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// View full vehicle photo gallery in popup carousel
function viewGallery(images, modelName) {
    if (!images || images.length === 0) {
        Swal.fire({ title: modelName, text: 'No photos uploaded for this vehicle.', icon: 'info' });
        return;
    }

    let slidesHtml = '';
    images.forEach((img, idx) => {
        const activeClass = idx === 0 ? 'active' : '';
        const imgUrl = '<?php echo BASE_URL; ?>uploads/' + img;
        slidesHtml += `<div class="carousel-item ${activeClass}"><img src="${imgUrl}" class="d-block w-100 rounded-3" style="max-height: 400px; object-fit: cover;" alt="Photo ${idx+1}"></div>`;
    });

    let carouselHtml = `
        <div id="galleryPopupCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
            <div class="carousel-inner">${slidesHtml}</div>
            ${images.length > 1 ? `
                <button class="carousel-control-prev" type="button" data-bs-target="#galleryPopupCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#galleryPopupCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
            ` : ''}
        </div>
    `;

    Swal.fire({
        title: modelName,
        html: carouselHtml,
        showCloseButton: true,
        showConfirmButton: false,
        width: '650px',
        customClass: { popup: 'rounded-4' }
    });

    setTimeout(() => {
        const el = document.getElementById('galleryPopupCarousel');
        if (el && typeof bootstrap !== 'undefined') {
            new bootstrap.Carousel(el, { interval: 2500, ride: 'carousel' });
        }
    }, 200);
}

// Live preview & validation when user selects new image files
function handleImageSelection(input) {
    const maxAllowed = 4;
    const badge = document.getElementById('imageCountBadge');
    const container = document.getElementById('imagePreviewContainer');
    
    if (input.files && input.files.length > maxAllowed) {
        Swal.fire({
            title: 'Maximum 4 Photos',
            text: 'You can only upload a maximum of 4 photos per vehicle.',
            icon: 'warning',
            confirmButtonColor: '#2563eb'
        });
        input.value = ''; // Reset selection
        if (badge) badge.style.display = 'none';
        if (container) container.style.display = 'none';
        return false;
    }

    if (input.files && input.files.length > 0) {
        if (badge) {
            badge.innerText = `✓ ${input.files.length} photo(s) selected (Maximum 4 allowed)`;
            badge.style.display = 'block';
        }
        
        if (container) {
            container.innerHTML = '';
            container.style.display = 'flex';

            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'position-relative border rounded-3 overflow-hidden shadow-sm me-1 mb-1';
                    div.style.width = '100px';
                    div.style.height = '75px';
                    div.innerHTML = `
                        <img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">
                        <span class="position-absolute bottom-0 start-0 bg-dark text-white opacity-75 px-1 py-0" style="font-size:10px;">Photo ${index + 1}</span>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
    } else {
        if (badge) badge.style.display = 'none';
    }
}

// Render thumbnails for existing car photos in edit modal
function renderExistingImagePreviews(imgJsonOrString) {
    const container = document.getElementById('imagePreviewContainer');
    if (!container) return;

    let imgs = [];
    if (imgJsonOrString) {
        try {
            const parsed = JSON.parse(imgJsonOrString);
            if (Array.isArray(parsed)) imgs = parsed;
        } catch(e) {
            imgs = imgJsonOrString.split(',').map(s => s.trim()).filter(Boolean);
        }
    }

    if (imgs.length > 0) {
        container.innerHTML = '';
        container.style.display = 'flex';

        imgs.forEach((img, idx) => {
            const imgUrl = '<?php echo BASE_URL; ?>uploads/' + img;
            const div = document.createElement('div');
            div.className = 'position-relative border rounded-3 overflow-hidden shadow-sm me-1 mb-1';
            div.style.width = '100px';
            div.style.height = '75px';
            div.style.cursor = 'pointer';
            div.onclick = function() {
                Swal.fire({ imageUrl: imgUrl, imageAlt: 'Photo ' + (idx + 1), showConfirmButton: false, showCloseButton: true });
            };
            div.innerHTML = `
                <img src="${imgUrl}" style="width:100%; height:100%; object-fit:cover;" title="Click to view full photo">
                <span class="position-absolute bottom-0 start-0 bg-dark text-white opacity-75 px-1 py-0" style="font-size:10px;">Photo ${idx + 1}</span>
            `;
            container.appendChild(div);
        });
    } else {
        container.style.display = 'none';
        container.innerHTML = '';
    }
}

function editCar(car){
    document.getElementById('modalTitle').innerText = 'Edit Car';
    document.getElementById('car_id').value = car.id;
    document.getElementById('brand').value = car.brand || '';
    document.getElementById('plate_number').value = car.plate_number || '';
    document.getElementById('model').value = car.model || '';
    document.getElementById('year').value = car.year || '';
    document.getElementById('price').value = car.price_per_day || '';
    document.getElementById('quantity').value = car.quantity || '1';
    document.getElementById('description').value = car.description || '';
    document.getElementById('status').value = car.status || 'available';
    document.getElementById('type').value = car.type || 'sedan';
    document.getElementById('current_image').value = car.image || '';
    
    const badge = document.getElementById('imageCountBadge');
    if (badge) badge.style.display = 'none';

    renderExistingImagePreviews(car.image);

    var myModal = new bootstrap.Modal(document.getElementById('carModal'));
    myModal.show();
}

function resetForm(){
    document.getElementById('modalTitle').innerText = 'Add New Car';
    document.getElementById('car_id').value = '';
    document.getElementById('brand').value = '';
    document.getElementById('plate_number').value = '';
    document.getElementById('quantity').value = '1';
    document.getElementById('type').value = 'sedan';
    document.querySelector('#carModal form').reset();

    const badge = document.getElementById('imageCountBadge');
    if (badge) badge.style.display = 'none';

    const container = document.getElementById('imagePreviewContainer');
    if (container) {
        container.innerHTML = '';
        container.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
