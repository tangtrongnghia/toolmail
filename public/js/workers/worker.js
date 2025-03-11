self.onmessage = async (event) => {
    let isSuccess = false
    let attempts = 0
    const maxAttempts = 100

    while (!isSuccess && attempts < maxAttempts) {
        try {
            const response = await fetch(
                'https://api.sptmail.com/api/otp-services/mail-otp-rental/?otpServiceCode=facebook&apiKey=' +
                    event.data.apiKey,
                {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                },
            )

            const data = await response.json()

            if (data.success) {
                isSuccess = true
                self.postMessage(data.gmail) // Gửi kết quả về main thread
            }
        } catch (error) {
            console.error('Worker request failed, retrying...')
        }

        attempts++
        await new Promise((resolve) => setTimeout(resolve, 1000))
    }
}
