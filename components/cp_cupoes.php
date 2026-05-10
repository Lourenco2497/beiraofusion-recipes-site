<?php
require_once '../connections/connections.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'];
$link = new_db_connection();

/* --- challenge checking functions --- */

function has_published_recipe($link, $user_id): bool
{
    $query = "SELECT COUNT(recipe_id) FROM recipes WHERE ref_user_id = ? AND ref_status_id = 2";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $count > 0;
}

function has_changed_profile_picture($link, $user_id): bool
{
    $query = "SELECT profile_image FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $profile_image);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return !empty($profile_image);
}

function has_favorited_recipe($link, $user_id): bool
{
    $query = "SELECT COUNT(ref_recipe_id) FROM recipe_likes WHERE ref_user_id = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $count > 0;
}

$challenge_checkers = [
    'PUBLISH_RECIPE' => 'has_published_recipe',
    'UPDATE_AVATAR' => 'has_changed_profile_picture',
    'FAVORITE_RECIPE' => 'has_favorited_recipe'
];

/* --- main logic to fetch and display coupons --- */

$query = "SELECT
            v.vouchers_id,
            v.name,
            v.description,
            v.image_url,
            v.valid_until,
            vt.name AS voucher_type_name,
            c.challenge_key,
            c.description AS challenge_description,
            (SELECT COUNT(*) FROM vouchers_users WHERE ref_vouchers_id = v.vouchers_id AND ref_user_id = ?) AS is_claimed
        FROM
            vouchers v
        LEFT JOIN
            vouchers_type vt ON v.ref_vouchers_type_id = vt.vouchers_type_id
        LEFT JOIN
            coupon_challenges cc ON v.vouchers_id = cc.ref_voucher_id
        LEFT JOIN
            challenges c ON cc.ref_challenge_id = c.challenge_id";

$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo '<p class="text-center mt-5">De momento não tens cupões disponíveis.</p>';
} else {
    while ($voucher = mysqli_fetch_assoc($result)) {
        $is_locked = false;
        if (!$voucher['is_claimed'] && !empty($voucher['challenge_key'])) {
            $checker_function = $challenge_checkers[$voucher['challenge_key']] ?? null;
            if ($checker_function && !$checker_function($link, $user_id)) {
                $is_locked = true;
            }
        }

        // data de validade
        $validity_text = '';
        if (!empty($voucher['valid_until'])) {
            $valid_date = date_create($voucher['valid_until']);
            $validity_text = '<p class="validity-text">Válido até: ' . date_format($valid_date, 'd/m/Y') . '</p>';
        }

        if ($is_locked) {
            // display locked voucher
            echo '
            <div class="ticket coupon-disabled">
                <div class="lock-icon">🔒</div>
                <div class="notch left"></div>
                <div class="left_text">
                    <img src="../imgs/' . htmlspecialchars($voucher['image_url']) . '" alt="' . htmlspecialchars($voucher['name']) . '">
                </div>
                <div class="right_text_bloqueado">
                    <h2>' . htmlspecialchars($voucher['name']) . '</h2>
                    <p>' . htmlspecialchars($voucher['description']) . '</p>
                    ' . $validity_text . '
                    <p class="unlock-text fs-6 fw-bold">' . htmlspecialchars($voucher['challenge_description']) . '</p>
                </div>
                <div class="notch right"></div>
            </div>';
        } else {

            $color_class = !empty($voucher['voucher_type_name'])
                ? 'coupon-' . strtolower(htmlspecialchars($voucher['voucher_type_name']))
                : 'coupon-default';

            echo '
            <div class="ticket ' . $color_class . '">
                <div class="notch left"></div>
                <div class="left_text">
                    <img src="../imgs/' . htmlspecialchars($voucher['image_url']) . '" alt="' . htmlspecialchars($voucher['name']) . '">
                </div>
                <div class="right_text">
                    <h2>' . htmlspecialchars($voucher['name']) . '</h2>
                    <p>' . htmlspecialchars($voucher['description']) . '</p>
                    ' . $validity_text . '
                </div>
                <div class="notch right"></div>
            </div>';
        }
    }
}

mysqli_stmt_close($stmt);
mysqli_close($link);
?>