<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const resendButton = document.getElementById('resendCode');
    const resendTimer = document.getElementById('resendTimer');
    const countdownElement = document.getElementById('countdown');
    if (codeInput) {
        codeInput.addEventListener('input', function() { this.value = this.value.replace(/[^0-9]/g, ''); });
        codeInput.focus();
    }
    if (!resendButton) return;
    resendButton.addEventListener('click', function() {
        resendButton.disabled = true;
        resendButton.textContent = 'Отправка...';
        fetch("{{ route('two-factor.resend') }}", {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}"}
        }).then(async response => {
            const data = await response.json();
            if (!response.ok) throw data;
            return data;
        }).then(() => {
            resendButton.textContent = 'Код отправлен!';
            resendButton.classList.remove('auth-btn-outline');
            resendButton.classList.add('auth-btn-success');
            startTimer(60);
        }).catch(error => {
            resendButton.disabled = false;
            resendButton.textContent = 'Отправить код повторно';
            alert(error.error || 'Ошибка при отправке кода. Попробуйте позже.');
            if (error.retry_after) startTimer(error.retry_after);
        });
    });
    function startTimer(seconds) {
        resendButton.style.display = 'none';
        resendTimer.style.display = 'block';
        countdownElement.textContent = seconds;
        const timer = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                resendTimer.style.display = 'none';
                resendButton.style.display = 'inline-block';
                resendButton.disabled = false;
                resendButton.textContent = 'Отправить код повторно';
                resendButton.classList.remove('auth-btn-success');
                resendButton.classList.add('auth-btn-outline');
            }
        }, 1000);
    }
});
</script>
