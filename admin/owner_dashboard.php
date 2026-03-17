<?php
require_once '../config.php';

// Admin check
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

$owner_id = $_GET['id'] ?? null;
if(!$owner_id){
    header("Location: dashboard.php");
    exit;
}

$owner_name = "";
$res = $mysqli->query("SELECT name FROM users WHERE id = $owner_id");
if($res->num_rows > 0) {
    $owner_name = $res->fetch_assoc()['name'];
} else {
    header("Location: dashboard.php");
    exit;
}

// Handle Delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $mysqli->query("DELETE FROM cars WHERE id = $id");
    header("Location: owner_dashboard.php?id=$owner_id");
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
    $quantity = $_POST['quantity'] ?? 1;

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
        $stmt = $mysqli->prepare("UPDATE cars SET model=?, year=?, price_per_day=?, description=?, image=?, status=?, quantity=? WHERE id=? AND owner_id=?");
        $stmt->bind_param("sidsssiii", $model, $year, $price, $desc, $image, $status, $quantity, $car_id, $owner_id);
    } else {
        // Insert
        $stmt = $mysqli->prepare("INSERT INTO cars (model, year, price_per_day, description, image, status, quantity, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidsssii", $model, $year, $price, $desc, $image, $status, $quantity, $owner_id);
    }
    
    if($stmt->execute()){
        header("Location: owner_dashboard.php?id=$owner_id");
        exit;
    } else {
        $error = "Database Error: " . $stmt->error;
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$cars = $mysqli->query("SELECT * FROM cars WHERE owner_id = $owner_id ORDER BY created_at DESC");
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($owner_name); ?>'s Dashboard</h2>
            <p class="text-muted mb-0">Manage cars for this specific owner.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">Back to Owners</a>
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
                        <?php if($cars->num_rows > 0): ?>
                            <?php while($row = $cars->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php $img = $row['image'] ? '../uploads/'.$row['image'] : 'https://via.placeholder.com/60'; ?>
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
                                        <a href="?id=<?php echo $owner_id; ?>&delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this car?')">Delete</a>
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
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Add New Car</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
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
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
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
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Car</button>
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
