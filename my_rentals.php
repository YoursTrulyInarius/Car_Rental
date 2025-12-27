<?php
require_once 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT r.*, c.model, c.image FROM rentals r 
        JOIN cars c ON r.car_id = c.id 
        WHERE r.user_id = ? ORDER BY r.created_at DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">My Rentals</h2>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Rental request submitted successfully! waiting for approval.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Car</th>
                            <th>Rental Period</th>
                            <th>Total Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php $img = $row['image'] ? 'uploads/'.$row['image'] : 'https://via.placeholder.com/60'; ?>
                                            <img src="<?php echo $img; ?>" width="60" height="40" class="rounded object-fit-cover me-3">
                                            <span class="fw-bold"><?php echo htmlspecialchars($row['model']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($row['start_date'])); ?> 
                                        <i class="bi bi-arrow-right mx-1 text-muted small"></i> 
                                        <?php echo date('M d, Y', strtotime($row['end_date'])); ?>
                                    </td>
                                    <td class="fw-bold">₱<?php echo number_format($row['total_price'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = 'secondary';
                                        if($row['status'] == 'approved') $statusClass = 'success';
                                        if($row['status'] == 'rejected') $statusClass = 'danger';
                                        if($row['status'] == 'pending') $statusClass = 'warning';
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?> rounded-pill px-3"><?php echo ucfirst($row['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">You haven't rented any cars yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
