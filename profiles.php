<?php
// Start session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

// Include the database connection file
require_once 'db_config.php';

// Fetch all profiles from the database
$stmt = $pdo->query('SELECT p.id, p.name, p.created_at, p.type, p.icon_path, pr.promo_name FROM vpn_profiles p LEFT JOIN promos pr ON p.promo_id = pr.id ORDER BY p.name');
$profiles = $stmt->fetchAll();

include 'header.php';
?>

<div class="page-header">
    <h1><?php echo translate('vpn_profiles'); ?></h1>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0"><?php echo translate('manage_profiles'); ?></h3>
        <div>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-secondary" data-filter="all"><?php echo translate('all'); ?></button>
                <button type="button" class="btn btn-secondary" data-filter="Freemium"><?php echo translate('freemium'); ?></button>
                <button type="button" class="btn btn-secondary" data-filter="Premium"><?php echo translate('premium'); ?></button>
            </div>
            <a href="add_profile.php" class="btn btn-primary ml-2"><?php echo translate('add_new_profile'); ?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="profile-grid">
            <?php foreach ($profiles as $profile) : ?>
                <div class="profile-card" data-profile-id="<?php echo $profile['id']; ?>" data-profile-type="<?php echo htmlspecialchars($profile['type']); ?>">
                    <div class="profile-card-header">
                        <div class="signal-bars">
                            <div class="bar"></div>
                            <div class="bar"></div>
                            <div class="bar"></div>
                            <div class="bar"></div>
                        </div>
                        <img src="<?php echo !empty($profile['icon_path']) ? htmlspecialchars($profile['icon_path']) : 'assets/us.png'; ?>" alt="Profile Icon" class="profile-icon">
                        <div class="ping">
                            <span class="ping-value">--</span> ms
                        </div>
                        <h5 class="profile-name"><?php echo htmlspecialchars($profile['name']); ?></h5>
                    </div>
                    <div class="profile-card-body">
                        <?php
                        $badge_class = 'badge-secondary';
                        if ($profile['type'] === 'Premium') {
                            $badge_class = 'badge-success';
                        } elseif ($profile['type'] === 'Freemium') {
                            $badge_class = 'badge-warning';
                        }
                        ?>
                        <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($profile['type']); ?></span>
                        <?php if (!empty($profile['promo_name'])) : ?>
                            <p class="profile-promo"><?php echo htmlspecialchars($profile['promo_name']); ?></p>
                        <?php endif; ?>
                        <p class="profile-date"><?php echo date('M j, Y g:i A', strtotime($profile['created_at'])); ?></p>
                    </div>
                    <div class="profile-card-footer">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="edit_profile.php?id=<?php echo $profile['id']; ?>" class="btn btn-warning text-dark" style="background-color: #FFA500; border-color: #FF8C00; font-weight: bold;"><?php echo translate('edit'); ?></a>
                            <a href="delete_profile.php?id=<?php echo $profile['id']; ?>" class="btn btn-danger" style="background-color: #DC3545; border-color: #C82333; font-weight: bold;" onclick="return confirm('<?php echo htmlspecialchars(translate('are_you_sure_you_want_to_delete_this_profile'), ENT_QUOTES); ?>');"><?php echo translate('delete'); ?></a>
                            <a href="profile_monitoring.php?profile_id=<?php echo $profile['id']; ?>" class="btn btn-info" style="background-color: #17A2B8; border-color: #138496; font-weight: bold;"><?php echo translate('monitor'); ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.btn-group[role="group"] .btn');
    const profileCards = document.querySelectorAll('.profile-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Manage active button state
            filterButtons.forEach(btn => btn.classList.remove('btn-primary'));
            this.classList.add('btn-primary');

            const filter = this.getAttribute('data-filter');

            profileCards.forEach(card => {
                const profileType = card.getAttribute('data-profile-type');
                if (filter === 'all' || filter === profileType) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Default to 'all' filter
    document.querySelector('[data-filter="all"]').classList.add('btn-primary');

    // 在 updateProfileStatus 函数中添加颜色逻辑
function updateProfileStatus(profileCard) {
    const profileId = profileCard.dataset.profileId;
    if (!profileId) return;

    fetch(`api_get_ping.php?id=${profileId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching profile status:', data.error);
                return;
            }

            // Update ping value
            const pingElement = profileCard.querySelector('.ping-value');
            if (pingElement) {
                pingElement.textContent = data.ping;
                
                // Ping 颜色编码
                if (data.ping < 50) {
                    pingElement.style.color = '#25A300';
                } else if (data.ping < 100) {
                    pingElement.style.color = '#BB7600';
                } else {
                    pingElement.style.color = '#C00000';
                }
            }

            // Update signal bars - 使用内联样式
            const signalBars = profileCard.querySelectorAll('.signal-bars .bar');
            const signalStrength = data.signal_strength;
            
            // 确定信号颜色
            let signalColor;
            if (signalStrength > 75) {
                signalColor = '#007820'; // 优秀
            } else if (signalStrength > 50) {
                signalColor = '#E3FF00'; // 良好
            } else if (signalStrength > 25) {
                signalColor = '#f8961e'; // 一般
            } else {
                signalColor = '#FF0000'; // 差
            }
            
            // 设置每个条形的状态和颜色
            signalBars.forEach((bar, index) => {
                const threshold = (index + 1) * 25;
                if (signalStrength >= threshold) {
                    bar.style.backgroundColor = signalColor;
                    bar.style.opacity = '1';
                } else {
                    bar.style.backgroundColor = '#ccc'; // 非激活状态
                    bar.style.opacity = '0.5';
                }
            });
        })
        .catch(error => console.error('Error fetching profile status:', error));
}
    profileCards.forEach(card => {
        // Initial update
        updateProfileStatus(card);

        // Set interval for continuous updates
        setInterval(() => updateProfileStatus(card), 5000); // Update every 5 seconds
    });
});
</script>


<style>

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.profile-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 15px;
    text-align: center;
}

.profile-card-header {
    display: grid;
    grid-template-areas:
        "signal-bars icon ping"
        "name name name";
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    margin-bottom: 15px;
}

.profile-icon {
    grid-area: icon;
}

.signal-bars {
    grid-area: signal-bars;
    justify-self: start;
}

.ping {
    grid-area: ping;
    justify-self: end;
}

.profile-name {
    grid-area: name;
    text-align: center;
    margin-top: 10px;
}

.profile-icon {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 10px;
}

.profile-name {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
}

.profile-card-body {
    margin-bottom: 15px;
}

.profile-promo {
    font-size: 0.9rem;
    color: #333;
    margin-top: 5px;
    font-weight: bold;
}

.profile-date {
    font-size: 0.9rem;
    color: #666;
    margin-top: 10px;
}

.profile-card-footer {
    display: flex;
    justify-content: center;
}
</style>

<?php include 'footer.php'; ?>
