// Function that reactivates a user and shows a toast only when activating
document.addEventListener('DOMContentLoaded', function() {
    var switches = document.querySelectorAll('.form-check-input');

    switches.forEach(function(switchElem) {
        switchElem.addEventListener('change', function(e) {
            // Check if the checkbox is being activated (checked)
            if (this.checked) {
                var userId = this.id.replace('deleteSwitch', ''); // Extract user ID

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'reactivate_user.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onload = function() {
                    if (this.status === 200) {
                        console.log(this.responseText);
                        showToast(); // Call the function to show the toast after successful AJAX call
                    }
                };

                xhr.send('id=' + userId);
            }
        });
    });
});

function showToast() {
    var toastEl = new bootstrap.Toast(document.getElementById('customToast'));
    toastEl.show();
}
