<?php
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-shell">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="admin-main">
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($owner_name); ?>'s Dashboard</h2>
            <p class="text-muted mb-0">Manage cars for this specific owner.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">Back to Owners</a>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#carModal" onclick="resetForm()">
                <i class="bi bi-plus-lg me-2"></i>Add New Car
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Car Model</th>
                            <th>Year</th>
                            <th>Price/Day</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($cars) && $cars && $cars->num_rows > 0): ?>
                            <?php while($row = $cars->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php $img = $row['image'] ? BASE_URL . 'uploads/' . $row['image'] : 'https://via.placeholder.com/60'; ?>
                                            <img src="<?php echo $img; ?>" class="rounded-3 me-3" width="50" height="50" style="object-fit: cover;">
                                            <span class="fw-bold"><?php echo htmlspecialchars($row['model']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $row['year']; ?></td>
                                    <td>₱<?php echo number_format($row['price_per_day'], 2); ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td>
                                        <?php 
                                        $badge = 'success';
                                        if($row['status'] == 'rented') $badge = 'warning';
                                        if($row['status'] == 'maintenance') $badge = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $badge; ?> bg-opacity-10 text-<?php echo $badge; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light border me-1" onclick='editCar(<?php echo json_encode($row); ?>)'>Edit</button>
                                        <a href="<?php echo BASE_URL; ?>admin/owner_dashboard.php?id=<?php echo $owner_id; ?>&delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this car?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No cars added for this owner yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Car Modal -->
<div class="modal fade" id="carModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" action="<?php echo BASE_URL; ?>admin/owner_dashboard.php?id=<?php echo $owner_id; ?>" enctype="multipart/form-data">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Add New Car</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="car_id" id="car_id">
                    <input type="hidden" name="current_image" id="current_image">

                    <div id="ownerCarEntryContainer">
                        <div class="car-entry border rounded-4 p-3 mb-3 bg-light-subtle">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong class="text-primary">Car 1</strong>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill remove-car-entry" style="display:none;">Remove</button>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Brand</label>
                                    <input type="text" name="cars[0][brand]" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Car Model</label>
                                    <input type="text" name="cars[0][model]" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Plate Number</label>
                                    <input type="text" name="cars[0][plate_number]" class="form-control" required>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label class="form-label">Year</label>
                                    <input type="number" name="cars[0][year]" class="form-control" required>
                                </div>
                                <div class="col">
                                    <label class="form-label">Price per Day (₱)</label>
                                    <input type="number" step="0.01" name="cars[0][price]" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Stock (Quantity)</label>
                                <input type="number" name="cars[0][quantity]" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="cars[0][description]" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="cars[0][status]" class="form-select">
                                    <option value="available">Available</option>
                                    <option value="rented">Rented</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Image</label>
                                <input type="file" name="image" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" id="addOwnerCarRowBtn" class="btn btn-outline-primary rounded-pill px-3">+ Add Another Car</button>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Cars</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addOwnerCarEntryRow() {
    const container = document.getElementById('ownerCarEntryContainer');
    if (!container) return;

    const index = container.querySelectorAll('.car-entry').length;
    const row = document.createElement('div');
    row.className = 'car-entry border rounded-4 p-3 mb-3 bg-light-subtle';
    row.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong class="text-primary">Car ${index + 1}</strong>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill remove-car-entry">Remove</button>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-md-5"><label class="form-label">Brand</label><input type="text" name="cars[${index}][brand]" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Car Model</label><input type="text" name="cars[${index}][model]" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Plate Number</label><input type="text" name="cars[${index}][plate_number]" class="form-control" required></div>
        </div>
        <div class="row g-2 mb-3">
            <div class="col"><label class="form-label">Year</label><input type="number" name="cars[${index}][year]" class="form-control" required></div>
            <div class="col"><label class="form-label">Price per Day (₱)</label><input type="number" step="0.01" name="cars[${index}][price]" class="form-control" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Total Stock (Quantity)</label><input type="number" name="cars[${index}][quantity]" class="form-control" min="1" value="1" required></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea name="cars[${index}][description]" class="form-control" rows="3"></textarea></div>
        <div class="mb-3"><label class="form-label">Status</label><select name="cars[${index}][status]" class="form-select"><option value="available">Available</option><option value="rented">Rented</option><option value="maintenance">Maintenance</option></select></div>
        <div class="mb-0"><label class="form-label">Image</label><input type="file" name="image" class="form-control"></div>
    `;
    container.appendChild(row);

    row.querySelector('.remove-car-entry').addEventListener('click', function() {
        row.remove();
        renumberOwnerCarEntries();
    });
}

function renumberOwnerCarEntries() {
    const entries = document.querySelectorAll('#ownerCarEntryContainer .car-entry');
    entries.forEach((entry, index) => {
        entry.querySelector('strong').textContent = 'Car ' + (index + 1);
        const fields = entry.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            const oldName = field.getAttribute('name');
            if (oldName) {
                field.setAttribute('name', oldName.replace(/cars\[\d+\]/, 'cars[' + index + ']'));
            }
        });
    });
}

function editCar(car){
    document.getElementById('modalTitle').innerText = 'Edit Car';
    document.getElementById('car_id').value = car.id;
    document.getElementById('brand').value = car.brand || '';
    document.getElementById('plate_number').value = car.plate_number || '';
    document.getElementById('model').value = car.model;
    document.getElementById('year').value = car.year;
    document.getElementById('price').value = car.price_per_day;
    document.getElementById('quantity').value = car.quantity;
    document.getElementById('description').value = car.description;
    document.getElementById('status').value = car.status;
    document.getElementById('current_image').value = car.image;
    
    var myModal = new bootstrap.Modal(document.getElementById('carModal'));
    myModal.show();
}

function resetForm(){
    document.getElementById('modalTitle').innerText = 'Add New Car';
    document.getElementById('car_id').value = '';
    const container = document.getElementById('ownerCarEntryContainer');
    if (container) {
        container.innerHTML = `
            <div class="car-entry border rounded-4 p-3 mb-3 bg-light-subtle">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <strong class="text-primary">Car 1</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill remove-car-entry" style="display:none;">Remove</button>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-5"><label class="form-label">Brand</label><input type="text" name="cars[0][brand]" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Car Model</label><input type="text" name="cars[0][model]" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Plate Number</label><input type="text" name="cars[0][plate_number]" class="form-control" required></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col"><label class="form-label">Year</label><input type="number" name="cars[0][year]" class="form-control" required></div>
                    <div class="col"><label class="form-label">Price per Day (₱)</label><input type="number" step="0.01" name="cars[0][price]" class="form-control" required></div>
                </div>
                <div class="mb-3"><label class="form-label">Total Stock (Quantity)</label><input type="number" name="cars[0][quantity]" class="form-control" min="1" value="1" required></div>
                <div class="mb-3"><label class="form-label">Description</label><textarea name="cars[0][description]" class="form-control" rows="3"></textarea></div>
                <div class="mb-3"><label class="form-label">Status</label><select name="cars[0][status]" class="form-select"><option value="available">Available</option><option value="rented">Rented</option><option value="maintenance">Maintenance</option></select></div>
                <div class="mb-0"><label class="form-label">Image</label><input type="file" name="image" class="form-control"></div>
            </div>
        `;
    }
    document.querySelector('#carModal form').reset();
}

document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('addOwnerCarRowBtn');
    if (addButton) {
        addButton.addEventListener('click', addOwnerCarEntryRow);
    }
    document.querySelectorAll('.remove-car-entry').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const row = btn.closest('.car-entry');
            if (row) {
                row.remove();
                renumberOwnerCarEntries();
            }
        });
    });
});
</script>

    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
