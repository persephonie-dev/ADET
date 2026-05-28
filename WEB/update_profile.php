<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once __DIR__ . '/api/config.php';

    // authentication check
    if (empty($_SESSION['user_id'])) 
    {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    $pageTitle = 'Profile Settings';
    $bodyClass = 'update-profile-page';

    $successMessage = '';
    $errorMessage = '';

    $userId = (int)$_SESSION['user_id'];


    $user = 
    [
       'first_name'   => '',
       'middle_name'  => '',
       'last_name'    => '',
       'DOB'          => '',
       'street_adr'   => '',
       'city'         => '',
       'region'       => '',
       'email'        => '',
       'phone_number' => ''
    ];

    $calculatedAge = 'Not Specified';

    try 
    {
        $stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, DOB, street_adr, city, region, email, phone_number FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $fetchedUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetchedUser) 
        {
            $user = $fetchedUser;
            
        
            if (empty($user['DOB']) || $user['DOB'] === '0000-00-00') 
            {
                $user['DOB'] = '';
                $calculatedAge = 'Not Specified';
            } else 
            {
                try
                {
                    $birthDate = new DateTime($user['DOB']);
                    $currentDate = new DateTime();
                    $calculatedAge = $currentDate->diff($birthDate)->y . ' Years Old';
                } 
                catch (Exception $e) 
                {
                    $calculatedAge = 'Not Specified';
                }
            }
        } 
        else 
        {
            session_destroy();
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    } catch (PDOException $e) 
    {
        $errorMessage = 'Database Error: ' . $e->getMessage();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') 
        {
        
            $firstName   = trim($_POST['first_name'] ?? '');
            $middleName  = trim($_POST['middle_name'] ?? '');
            $lastName    = trim($_POST['last_name'] ?? '');
            $dob         = trim($_POST['DOB'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $phoneNumber = trim($_POST['phone_number'] ?? '');
            $streetAdr   = trim($_POST['street_adr'] ?? '');
            $city        = trim($_POST['city'] ?? '');
            $region      = trim($_POST['region'] ?? '');

            // password 
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword     = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $passwordChanged = false;
            $newPasswordHash = '';

            // validate fields
            if (empty($firstName) || empty($lastName) || empty($dob) || empty($email) || empty($streetAdr) || empty($city) || empty($region)) 
            {
                $errorMessage = 'All fields marked with an asterisk (*) are mandatory.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
            {
                $errorMessage = 'Please provide a valid, well-formed email address.';
            }
            // for editing password
            elseif (!empty($newPassword)) 
            {
                if (empty($currentPassword)) 
                {
                    $errorMessage = 'Enter Current Password.';
                } 
                elseif ($newPassword !== $confirmPassword) 
                {
                    $errorMessage = 'Inputted Password does not match.';
                } 
                elseif (strlen($newPassword) < 8) 
                {
                    $errorMessage = 'The new password must be at least 8 characters long.';
                } 
                else 
                {
                    try 
                    {
                        
                        $authStmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
                        $authStmt->execute([$userId]);
                        $accountRow = $authStmt->fetch(PDO::FETCH_ASSOC);

                        if ($accountRow && password_verify($currentPassword, $accountRow['password_hash'])) 
                        {
                            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                            $passwordChanged = true;
                        } 
                        else 
                        {
                            $errorMessage = 'The current password entered does not match any of our record.';
                        }
                    } 
                    catch (PDOException $e) 
                    {
                        $errorMessage = 'A failure inside the security sub-routine rejected verification.';
                    }
                }
            }

            // execute db update if validation is a success
            if (empty($errorMessage)) 
            {
                try 
                {
                    if ($passwordChanged) 
                        {
                        // update profile details AND update the password hash
                        $updateStmt = $pdo->prepare("
                            UPDATE users 
                            SET first_name = ?, middle_name = ?, last_name = ?, DOB = ?, 
                                email = ?, phone_number = ?, street_adr = ?, city = ?, region = ?,
                                password_hash = ?
                            WHERE user_id = ?
                        ");
                        $status = $updateStmt->execute([
                            $firstName, $middleName, $lastName, $dob, 
                            $email, $phoneNumber, $streetAdr, $city, $region,
                            $newPasswordHash, $userId
                        ]);
                    } else 
                    {
                        //No password change requested
                        $updateStmt = $pdo->prepare("
                            UPDATE users 
                            SET first_name = ?, middle_name = ?, last_name = ?, DOB = ?, 
                                email = ?, phone_number = ?, street_adr = ?, city = ?, region = ? 
                            WHERE user_id = ?
                        ");
                        $status = $updateStmt->execute([
                            $firstName, $middleName, $lastName, $dob, 
                            $email, $phoneNumber, $streetAdr, $city, $region, $userId
                        ]);
                    }
                    
                    if ($status) 
                    {
                        $user['first_name']   = $firstName;
                        $user['middle_name']  = $middleName;
                        $user['last_name']    = $lastName;
                        $user['DOB']          = $dob;
                        $user['email']        = $email;
                        $user['phone_number'] = $phoneNumber;
                        $user['street_adr']   = $streetAdr;
                        $user['city']         = $city;
                        $user['region']       = $region;

                        if (!empty($dob)) 
                        {
                            $birthDate = new DateTime($dob);
                            $currentDate = new DateTime();
                            $calculatedAge = $currentDate->diff($birthDate)->y . ' Years Old';
                        } else 
                        {
                            $calculatedAge = 'Not Specified';
                        }

                        $successMessage = 'Your profile information has been successfully updated.';
                    } else 
                    {
                        $errorMessage = 'An error occurred while attempting to save modifications to your profile record.';
                    }
                } catch (PDOException $e) 
                {
                    $errorMessage = 'A database error blocked the profile update: ' . $e->getMessage();
                }
            }
        } 
        elseif ($action === 'delete_account') 
        {
            $verifyPassword = $_POST['verify_password'] ?? '';

            if (empty($verifyPassword)) 
            {
                $errorMessage = 'Account removal requires your current account password to re-authenticate.';
            } else 
            {
                try 
                {
                    $authStmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
                    $authStmt->execute([$userId]);
                    $accountRow = $authStmt->fetch(PDO::FETCH_ASSOC);

                    if ($accountRow && password_verify($verifyPassword, $accountRow['password_hash'])) 
                    {
                        $deleteStmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                        $deleteStmt->execute([$userId]);
                        
                        session_destroy();
                        header('Location: ' . BASE_URL . '/index.php?account_deleted=true');
                        exit;
                    } 
                    else 
                    {
                        $errorMessage = 'The password entered does not match our security records. Authorization denied.';
                    }
                } catch (PDOException $e) 
                 {
                    $errorMessage = 'A failure inside the database sub-routines rejected this removal sequence.';
                }
            }
        }
    }


    function renderInputStatus($value) 
    {
        if ($value === null || trim($value) === '' || $value === '0000-00-00') 
        {
            return 'style="background: var(--white);"';
        }
        return 'readonly class="read-only-display"';
    }

require_once __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profile_setting.css">

<div class="container" style="margin-top: 6rem; margin-bottom: 6rem;">
    
    <div class="center" style="margin-bottom: 3rem;">
        <span class="section-label">Guest Portal</span>
        <h2>Account Settings</h2>
        <div class="gold-line"></div>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div style="background: rgba(39, 174, 96, 0.1); border-left: 3px solid var(--success); color: var(--success); padding: 1.25rem; margin-bottom: 2rem; font-size: 0.9rem; border-radius: var(--radius);">
            <?= h($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div style="background: rgba(192, 57, 43, 0.1); border-left: 3px solid var(--danger); color: var(--danger); padding: 1.25rem; margin-bottom: 2rem; font-size: 0.9rem; border-radius: var(--radius);">
            <?= h($errorMessage) ?>
        </div>
    <?php endif; ?>

    <div class="profile-dashboard">
        
        <div class="profile-sidebar-pane">
            <div class="panel-card" style="text-align: center; background: var(--forest); color: var(--cream);">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--gold); color: var(--forest); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-family: var(--font-display); font-size: 2rem; font-weight: 600;">
                    <?= strtoupper(substr($user['first_name'] ?? 'G', 0, 1) . substr($user['last_name'] ?? 'P', 0, 1)) ?>
                </div>
                <h3 style="color: var(--white); margin-bottom: 0.25rem; font-size: 1.4rem;">
                    <?= h(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
                </h3>
                <p style="font-size: 0.75rem; color: rgba(248, 244, 236, 0.6); text-transform: uppercase; letter-spacing: 0.05em;">
                    Verified Hotel Guest
                </p>
            </div>

            <div class="panel-card">
                <h3 class="panel-card-title" style="font-size: 1.2rem;">System Diagnostics</h3>
                
                <div class="qb-field" style="margin-bottom: 1.25rem;">
                    <label>Calculated Age <span class="verified-badge">Live</span></label>
                    <input type="text" class="read-only-display" value="<?= h($calculatedAge) ?>" readonly>
                </div>

                <p style="font-size: 0.75rem; color: var(--muted); line-height: 1.4; text-align: justify; margin-top: 1rem;">
                    Your age is dynamically compiled based on your recorded Date of Birth value to comply with check-in requirements.
                </p>
            </div>
        </div>

        <div class="profile-main-pane">
            
            <div class="panel-card">
                <h3 class="panel-card-title">Modify Profile Information</h3>
                
                <form action="<?= h($_SERVER['PHP_SELF']) ?>" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="name-grid">
                        <div class="qb-field">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?= h($user['first_name'] ?? '') ?>" required style="background: var(--white);">
                        </div>
                        <div class="qb-field">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="<?= h($user['middle_name'] ?? '') ?>" style="background: var(--white);">
                        </div>
                        <div class="qb-field">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?= h($user['last_name'] ?? '') ?>" required style="background: var(--white);">
                        </div>
                    </div>

                    <div class="qb-field">
                        <label for="DOB">Date of Birth *</label>
                        <input type="date" id="DOB" name="DOB" value="<?= h($user['DOB'] ?? '') ?>" required <?= renderInputStatus($user['DOB']) ?>>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--cream-dark); margin: 0.5rem 0;">

                    <div class="qb-field">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?= h($user['email'] ?? '') ?>" required style="background: var(--white);">
                    </div>

                    <div class="qb-field">
                        <label for="phone_number">Contact Number</label>
                        <input type="text" id="phone_number" name="phone_number" value="<?= h($user['phone_number'] ?? '') ?>" placeholder="Enter your telephone/mobile number" style="background: var(--white);">
                    </div>

                    <div class="address-grid">
                        <div class="qb-field">
                            <label for="street_adr">Street Address *</label>
                            <input type="text" id="street_adr" name="street_adr" value="<?= h($user['street_adr'] ?? '') ?>" required style="background: var(--white);">
                        </div>
                        <div class="qb-field">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" value="<?= h($user['city'] ?? '') ?>" required style="background: var(--white);">
                        </div>
                        <div class="qb-field">
                            <label for="region">Region *</label>
                            <input type="text" id="region" name="region" value="<?= h($user['region'] ?? '') ?>" required style="background: var(--white);">
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--cream-dark); margin: 0.5rem 0;">
                    <h4 style="font-family: var(--font-display); color: var(--forest); font-size: 1.2rem; margin: 0;">Security Credentials</h4>
                    <p style="font-size: 0.82rem; color: var(--muted); margin: 0 0 0.5rem 0;">Leave the fields below completely blank if you do not want to alter your account password.</p>
                    
                    <div class="qb-field">
                        <label for="current_password">Current Login Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Required only if changing password" style="background: var(--white);">
                    </div>

                    <div class="name-grid">
                        <div class="qb-field">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Minimum 8 characters" style="background: var(--white);">
                        </div>
                        <div class="qb-field">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your new password" style="background: var(--white);">
                        </div>
                        <div></div> </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem; justify-content: flex-end;">
                        <a href="<?= BASE_URL ?>/profile.php" class="btn btn-outline" style="border: 1px solid var(--cream-dark); color: var(--charcoal); padding: 0.7rem 1.5rem;">
                            Discard Changes
                        </a>
                        <button type="submit" class="btn btn-gold" style="padding: 0.7rem 2rem;">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            <div class="panel-card danger-zone">
                <h3 class="danger-title">Deactivate Account</h3>
                <p style="font-size: 0.88rem; color: var(--muted); margin-bottom: 1.5rem; line-height: 1.5;">
                    Permanently delete guest profiles, ongoing room bookings, and loyalty statements. Active identity validation confirmation is mandatory.
                </p>

                <form action="<?= h($_SERVER['PHP_SELF']) ?>" method="POST" onsubmit="return confirm('Confirm permanent deletion of this guest profile? This operation cannot be reversed.');" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <input type="hidden" name="action" value="delete_account">

                    <div class="qb-field" style="max-width: 450px;">
                        <label for="verify_password" style="color: var(--danger); font-weight: 600;">Confirm Password to Proceed</label>
                        <input type="password" id="verify_password" name="verify_password" placeholder="Enter your login password" required style="background: var(--white); border-color: rgba(192, 57, 43, 0.2); width: 100%;">
                    </div>

                    <div>
                        <button type="submit" class="btn" style="background: var(--danger); color: var(--white); border-color: var(--danger); padding: 0.7rem 1.5rem;">
                            Permanently Delete Profile
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

</body>
</html>