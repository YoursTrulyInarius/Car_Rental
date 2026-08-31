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
                                <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Stock</th>
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
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="position-relative d-inline-block">
                                                <?php if($primary_img): ?>
                                                    <img src="<?php echo htmlspecialchars($primary_img); ?>" width="70" height="46" class="rounded-3 shadow-sm object-fit-cover" alt="Car">
                                                    <?php if(count($imgs) > 1): ?>
                                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
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
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['model']); ?></span>
                                                <small class="text-muted">Owner: <?php echo htmlspecialchars($row['owner_name'] ?? 'System'); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1 text-capitalize">
                                                <i class="bi bi-gear-wide-connected me-1 text-primary"></i>
                                                <?php echo htmlspecialchars(ucfirst($row['type'] ?? 'Sedan')); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted"><?php echo $row['year']; ?></td>
                                        <td class="fw-bold text-primary">₱<?php echo number_format($row['price_per_day'], 2); ?></td>
                                        <td class="fw-bold text-dark"><?php echo $row['quantity']; ?></td>
                                        <td>
                                            <?php 
                                            $car_id = $row['id'];
                                            $stock = $row['quantity'];
                                            $today = date('Y-m-d');
                                            $check = $this->carModel->getActiveBookingsCount($car_id, $today);
                                            
                                            $real_status = $row['status'];
                                            if($stock - $check <= 0){
                                                $real_status = 'rented';
                                            }

                                            $statusClass = match($real_status) {
                                                'available' => 'success',
                                                'rented' => 'warning',
                                                'maintenance' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?> bg-opacity-10 text-<?php echo $statusClass; ?> rounded-pill px-3 py-2">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                                <?php echo ucfirst($real_status); ?>
                                            </span>
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
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Car Model / Brand</label>
                            <input type="text" name="model" id="model" class="form-control rounded-3" placeholder="e.g. Toyota Camry 2.5V" required>
                        </div>
                        <div class="col-md-4">
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
                            <label class="form-label fw-bold">Total Stock (Quantity)</label>
                            <input type="number" name="quantity" id="quantity" class="form-control rounded-3" min="1" value="1" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="status" class="form-select rounded-3">
                                <option value="available">Available</option>
                                <option value="rented">Rented (Out of Stock)</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assign Owner / Manager</label>
                            <select name="owner_id" id="owner_id" class="form-select rounded-3">
                                <option value="">System / No Owner</option>
                                <?php 
                                if($all_owners) {
                                    $all_owners->data_seek(0);
                                    while($o = $all_owners->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $o['id']; ?>"><?php echo htmlspecialchars($o['name']); ?></option>
                                    <?php endwhile;
                                } ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="description" class="form-control rounded-3" rows="3" placeholder="Provide vehicle details, features, transmission type, fuel policy, etc."></textarea>
                    </div>

                    <!-- Multi-Image Upload Section (Upload at least 4 photos) -->
                    <div class="border rounded-3 p-3 bg-light mb-3">
                        <label class="form-label fw-bold d-block mb-1">
                            <i class="bi bi-images me-2 text-primary"></i>Vehicle Gallery Images (Upload up to 4+ photos)
                        </label>
                        <small class="text-muted d-block mb-3">Upload multiple photos to create an automated carousel preview for customers.</small>

                        <div class="mb-3">
                            <label class="form-label small text-uppercase fw-bold text-muted">Batch Upload (Select 4 photos at once)</label>
                            <input type="file" name="images[]" multiple class="form-control rounded-3" accept="image/*">
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Photo 1 (Primary Cover)</label>
                                <input type="file" name="image1" class="form-control form-control-sm rounded-3" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Photo 2</label>
                                <input type="file" name="image2" class="form-control form-control-sm rounded-3" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Photo 3</label>
                                <input type="file" name="image3" class="form-control form-control-sm rounded-3" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Photo 4</label>
                                <input type="file" name="image4" class="form-control form-control-sm rounded-3" accept="image/*">
                            </div>
                        </div>
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
function editCar(car){
    document.getElementById('modalTitle').innerText = 'Edit Car';
    document.getElementById('car_id').value = car.id;
    document.getElementById('model').value = car.model || '';
    document.getElementById('year').value = car.year || '';
    document.getElementById('price').value = car.price_per_day || '';
    document.getElementById('quantity').value = car.quantity || '1';
    document.getElementById('description').value = car.description || '';
    document.getElementById('status').value = car.status || 'available';
    document.getElementById('type').value = car.type || 'sedan';
    document.getElementById('current_image').value = car.image || '';
    if(car.owner_id) {
        document.getElementById('owner_id').value = car.owner_id;
    } else {
        document.getElementById('owner_id').value = '';
    }
    
    var myModal = new bootstrap.Modal(document.getElementById('carModal'));
    myModal.show();
}

function resetForm(ownerId = null){
    document.getElementById('modalTitle').innerText = 'Add New Car';
    document.getElementById('car_id').value = '';
    document.getElementById('quantity').value = '1';
    document.getElementById('type').value = 'sedan';
    document.querySelector('#carModal form').reset();
    if(ownerId) document.getElementById('owner_id').value = ownerId;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
