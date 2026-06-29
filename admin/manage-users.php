<?php
// admin/manage-users.php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/countries.php'; // ← added

// Handle form submission (update user)
$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if ($user_id <= 0) {
        $error = "Invalid user ID.";
    } else {
        $email             = trim($_POST['email'] ?? '');
        $phone             = trim($_POST['phone'] ?? '');
        $country           = trim($_POST['country'] ?? '');           // ← new
        $vip_level         = (int)($_POST['vip_level'] ?? 0);
        $balance           = (float)($_POST['balance'] ?? 0);
        $withdrawal_balance = (float)($_POST['withdrawal_balance'] ?? 0);

        // Password is optional
        $password_update = '';
        if (!empty($_POST['password'])) {
            $new_pass = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $password_update = ", password = :password";
        }

        try {
            $sql = "
                UPDATE users 
                SET 
                    email              = :email,
                    phone              = :phone,
                    country            = :country,
                    vip_level          = :vip_level,
                    balance            = :balance,
                    withdrawal_balance = :withdrawal_balance
                    $password_update
                WHERE id = :id
            ";

            $stmt = $pdo->prepare($sql);
            $params = [
                ':email'              => $email,
                ':phone'              => $phone !== '' ? $phone : null, // ✅ FIXED
                ':country'            => $country ? strtoupper($country) : null, // normalize to uppercase
                ':vip_level'          => $vip_level,
                ':balance'            => $balance,
                ':withdrawal_balance' => $withdrawal_balance,
                ':id'                 => $user_id
            ];

            if ($password_update) {
                $params[':password'] = $new_pass;
            }

            $stmt->execute($params);
            $message = "User #$user_id updated successfully.";
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
}

// Fetch all users (including country)
try {
    $stmt = $pdo->query("
        SELECT 
            id, email, phone, country,
            referred_by, vip_level, 
            balance, withdrawal_balance, created_at
        FROM users 
        ORDER BY id DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load users: " . $e->getMessage();
    $users = [];
}
?>

<main>
  <h1 style="text-align:center; margin: 2.5rem 0 2rem;">Manage Users</h1>

  <?php if ($message): ?>
    <div style="background:#238636; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:900px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background:#f85149; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:900px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($users)): ?>
    <p style="text-align:center; color:var(--text-muted); font-size:1.1rem;">No users found in the database.</p>
  <?php else: ?>

  <div style="overflow-x:auto; margin: 0 auto; max-width: 100%;">
    <table style="
      width:100%; 
      max-width: 1200px; 
      margin: 0 auto 3rem; 
      border-collapse: separate; 
      border-spacing: 0 12px; 
      background: transparent;
    ">
      <thead>
        <tr style="background:#1f2937; color:#e6edf3;">
          <th style="padding:1.2rem 1rem; font-weight:600; border-top-left-radius:8px;">ID</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Email</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Phone</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Country</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">VIP Level</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Balance</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Withdrawal Bal.</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Referred By</th>
          <th style="padding:1.2rem 1rem; font-weight:600;">Created</th>
          <th style="padding:1.2rem 1rem; font-weight:600; border-top-right-radius:8px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
        <tr style="background:var(--card); box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
          <td style="padding:1.3rem 1rem; border-radius:0 0 0 8px;"><?= htmlspecialchars($user['id']) ?></td>
          <td style="padding:1.3rem 1rem;"><?= htmlspecialchars($user['email'] ?? '-') ?></td>
          <td style="padding:1.3rem 1rem;"><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
          <td style="padding:1.3rem 1rem; text-align:center; font-weight:500;">
            <?= htmlspecialchars($user['country'] ?: '—') ?>
          </td>
          <td style="padding:1.3rem 1rem; text-align:center;"><?= htmlspecialchars($user['vip_level'] ?? '0') ?></td>
          <td style="padding:1.3rem 1rem; text-align:right;">$<?= number_format($user['balance'] ?? 0, 2) ?></td>
          <td style="padding:1.3rem 1rem; text-align:right;">$<?= number_format($user['withdrawal_balance'] ?? 0, 2) ?></td>
          <td style="padding:1.3rem 1rem;"><?= htmlspecialchars($user['referred_by'] ?? '-') ?></td>
          <td style="padding:1.3rem 1rem;"><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
          <td style="padding:1.3rem 1rem; border-radius:0 0 8px 0; text-align:center;">
            <button 
              class="btn" 
              style="padding:0.6rem 1.2rem; font-size:0.95rem;"
              onclick="openEditModal(
                <?= $user['id'] ?>, 
                '<?= addslashes($user['email'] ?? '') ?>', 
                '<?= addslashes($user['phone'] ?? '') ?>', 
                '<?= addslashes($user['country'] ?? '') ?>',
                <?= $user['vip_level'] ?? 0 ?>, 
                <?= $user['balance'] ?? 0 ?>, 
                <?= $user['withdrawal_balance'] ?? 0 ?>
              )"
            >
              <i class="fas fa-edit"></i> Edit
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>

  <!-- Edit Modal – now with country dropdown -->
  <div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); align-items:center; justify-content:center; z-index:1000;">
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; width:90%; max-width:520px; padding:2.2rem; position:relative; max-height:85vh; overflow-y:auto;">
      <button onclick="closeEditModal()" style="position:absolute; top:1rem; right:1.2rem; background:none; border:none; color:var(--text-muted); font-size:1.8rem; cursor:pointer;">×</button>
      
      <h2 style="margin-bottom:1.8rem; text-align:center;">Edit User</h2>
      
      <form method="POST">
        <input type="hidden" name="update_user" value="1">
        <input type="hidden" id="edit_user_id" name="user_id">

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Email</label>
          <input type="email" id="edit_email" name="email" required style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-size:1rem;">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Phone</label>
          <input type="text" id="edit_phone" name="phone" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-size:1rem;">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Country</label>
          <select id="edit_country" name="country" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-size:1rem;">
            <?php foreach ($countries as $code => $name): ?>
              <option value="<?= htmlspecialchars($code) ?>">
                <?= htmlspecialchars($name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">New Password <small>(leave blank to keep current)</small></label>
          <input type="password" name="password" placeholder="••••••••" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-size:1rem;">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">VIP Level</label>
          <input type="number" id="edit_vip_level" name="vip_level" min="0" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-size:1rem;">
        </div>

        <div style="margin-bottom:1.4rem;">
          <label style="display:block; margin-bottom:0.5rem;">Balance ($)</label>
          <input type="number" id="edit_balance" name="balance" step="0.01" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-size:1rem;">
        </div>

        <div style="margin-bottom:2rem;">
          <label style="display:block; margin-bottom:0.5rem;">Withdrawal Balance ($)</label>
          <input type="number" id="edit_withdrawal_balance" name="withdrawal_balance" step="0.01" style="width:100%; padding:0.8rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-size:1rem;">
        </div>

        <button type="submit" class="btn" style="width:100%; padding:1rem; font-size:1.05rem;">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </form>
    </div>
  </div>
</main>

<script>
function openEditModal(id, email, phone, country, vip_level, balance, withdrawal_balance) {
  document.getElementById('edit_user_id').value             = id;
  document.getElementById('edit_email').value               = email;
  document.getElementById('edit_phone').value               = phone;
  
  // Set country dropdown value
  const countrySelect = document.getElementById('edit_country');
  if (country) {
    countrySelect.value = country.toUpperCase(); // ensure uppercase match
  } else {
    countrySelect.value = ''; // default to "— Select Country —"
  }

  document.getElementById('edit_vip_level').value           = vip_level;
  document.getElementById('edit_balance').value             = balance;
  document.getElementById('edit_withdrawal_balance').value  = withdrawal_balance;
  document.getElementById('editModal').style.display        = 'flex';
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
