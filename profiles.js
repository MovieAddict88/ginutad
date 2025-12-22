document.addEventListener('DOMContentLoaded', function() {
    const profileCards = document.querySelectorAll('.profile-card');

    function updateProfileStatus(profileCard) {
        const profileId = profileCard.dataset.profileId;
        if (!profileId) return;

        fetch(`api_get_ping.php?profile_id=${profileId}`)
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
                }

                // Update signal bars
                const signalBars = profileCard.querySelectorAll('.signal-bars .bar');
                const signalStrength = data.signal;

                signalBars.forEach((bar, index) => {
                    if (index < signalStrength) {
                        bar.classList.add('active');
                    } else {
                        bar.classList.remove('active');
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
