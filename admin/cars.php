<?php
require_once '../config.php';

// Admin check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

// Handle Delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $mysqli->query("DELETE FROM cars WHERE id = $id");
    header("Location: cars.php");
    exit;
}

// Handle Add/Edit
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $status = $_POST['status'];
    $car_id = $_POST['car_id'] ?? null;

    // Image Upload
    $image = $_POST['current_image'] ?? '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "../uploads/";
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)){
            $image = $filename;
        }
    }

    if($car_id){
        // Update
        $stmt = $mysqli->prepare("UPDATE cars SET model=?, year=?, price_per_day=?, description=?, image=?, status=?, quantity=? WHERE id=?");
        $stmt->bind_param("sidsssii", $model, $year, $price, $desc, $image, $status, $_POST['quantity'], $car_id);
    } else {
        // Insert
        $stmt = $mysqli->prepare("INSERT INTO cars (model, year, price_per_day, description, image, status, quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsssi", $model, $year, $price, $desc, $image, $status, $_POST['quantity']);
    }
    
    if($stmt->execute()){
        header("Location: cars.php");
        exit;
    } else {
        $error = "Database Error: " . $stmt->error;
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$cars = $mysqli->query("SELECT * FROM cars ORDER BY created_at DESC");
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">Fleet Management</h2>
            <p class="text-muted mb-0">Add, edit, and manage your vehicle inventory.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#carModal" onclick="resetForm()">
            <i class="bi bi-plus-lg me-2"></i>Add New Car
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase text-muted small fw-bold tracking-wide">Vehicle</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Model</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Year</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Daily Rate</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Stock</th>
                            <th class="py-3 text-uppercase text-muted small fw-bold tracking-wide">Status</th>
                            <th class="text-end pe-4 py-3 text-uppercase text-muted small fw-bold tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $cars->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <?php if($row['image']): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" width="60" height="40" class="rounded-3 shadow-sm object-fit-cover" alt="Car">
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['model']); ?></span>
                                </td>
                                <td class="text-muted"><?php echo $row['year']; ?></td>
                                <td class="fw-bold text-primary">₱<?php echo number_format($row['price_per_day'], 2); ?></td>
                                <td class="fw-bold text-dark"><?php echo $row['quantity']; ?></td>
                                <td>
                                    <?php 
                                    // Check active rentals
                                    $car_id = $row['id'];
                                    $stock = $row['quantity'];
                                    $today = date('Y-m-d');
                                    $check = $mysqli->query("SELECT COUNT(*) FROM rentals WHERE car_id = $car_id AND status IN ('pending', 'approved') AND '$today' BETWEEN start_date AND end_date")->fetch_row()[0];
                                    
                                    $real_status = $row['status'];
                                    if($stock - $check <= 0){
                                        $real_status = 'rented'; // Force rented if out of stock
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
                                        Edit
                                    </button>
                                    <a href="cars.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-danger rounded-pill px-3 fw-bold hover-shadow" onclick="return confirm('Delete this car? This action cannot be undone.')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="carModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="cars.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Car</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="car_id" id="car_id">
                    <input type="hidden" name="current_image" id="current_image">
                    
                    <div class="mb-3">
                        <label class="form-label">Car Model</label>
                        <input type="text" name="model" id="model" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" id="year" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Price per Day (₱)</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Stock (Quantity)</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
                        <small class="text-muted">How many of this vehicle do you have?</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="available">Available</option>
                            <option value="rented">Rented (Out of Stock)</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control">
                        <small class="text-muted">Leave empty to keep current image (if editing).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Car</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCar(car){
    document.getElementById('modalTitle').innerText = 'Edit Car';
    document.getElementById('car_id').value = car.id;
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
    document.getElementById('quantity').value = '1';
    document.querySelector('form').reset();
}
</script>

<?php require_once '../includes/footer.php'; ?>
