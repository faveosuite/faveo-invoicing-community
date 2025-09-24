<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <!-- Styles -->
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --secondary-color: #f3f4f6;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --bg-color: #f9fafb;
            --card-bg: #ffffff;
            --success-color: #10b981;
            --error-color: #ef4444;
            --font-family: 'Inter', sans-serif;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 0.75rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background: var(--bg-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            width: 100%;
            max-width: 600px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .card-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 0.875rem;
            font-weight: 600;
            color: white;
            background: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1rem;
            text-align: center;
        }

        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .hidden {
            display: none;
        }

        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Summary Styles */
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: var(--light-text);
            font-weight: 500;
        }

        .summary-value {
            font-weight: 600;
            color: var(--text-color);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            margin-top: 1rem;
            border-top: 2px dashed #e5e7eb;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Success/Failed */
        .status-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .success-icon {
            background: var(--success-color);
        }

        .failed-icon {
            background: var(--error-color);
        }

        .status-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-align: center;
        }

        .status-message {
            text-align: center;
            color: var(--light-text);
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .ref-id {
            background: var(--secondary-color);
            padding: 0.75rem;
            border-radius: 0.5rem;
            text-align: center;
            font-family: monospace;
            font-size: 1.1rem;
            letter-spacing: 1px;
            color: var(--text-color);
            margin-bottom: 2rem;
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid var(--primary-color);
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div id="payment-app">
            <!-- Rendered Content Will Go Here -->
        </div>
    </div>

    <script>
        // Get API base from current URL (works dynamically)
        const API_BASE = window.location.origin + window.location.pathname.replace(/\/+$/, '').replace(/\/open-payment$/, '') + '/open-payment';

        // --- Views ---

        const renderForm = () => {
            const html = `
            <div class="card fade-in">
                <div class="card-header">
                    <h1>Secure Payment</h1>
                    <p>Enter your details to proceed</p>
                </div>
                <div class="card-body">
                    <form id="paymentForm">
                        <!-- Personal Info -->
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="John Doe">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" required placeholder="john@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" name="mobile" required placeholder="+1234567890">
                        </div>
                        
                        <!-- Address Info -->
                        <div class="form-group">
                            <label class="form-label">Company (Optional)</label>
                            <input type="text" class="form-control" name="company" placeholder="Acme Inc.">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" required placeholder="123 Main St">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" required placeholder="New York">
                            </div>
                            <div class="form-group">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="state" required placeholder="NY">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Zip Code</label>
                                <input type="text" class="form-control" name="zip" required placeholder="10001">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" required placeholder="USA">
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount" required min="1" step="0.01" placeholder="0.00">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Currency</label>
                                <select class="form-select" name="currency">
                                    <option value="USD">USD ($)</option>
                                    <option value="INR">INR (₹)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Payment Gateway</label>
                                <select class="form-select" name="gateway">
                                    <option value="Razorpay">Razorpay</option>
                                    <option value="Stripe">Stripe</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Payment for..."></textarea>
                        </div>
                        
                        <div id="error-msg" style="color: red; margin-bottom: 1rem; display: none;"></div>

                        <button type="submit" class="btn" id="submitBtn">Proceed to Summary</button>
                    </form>
                </div>
            </div>
        `;
            $('#payment-app').html(html);

            $('#paymentForm').on('submit', function (e) {
                e.preventDefault();
                const btn = $('#submitBtn');
                const originalText = btn.text();
                btn.prop('disabled', true).html('<div class="loader"></div>');
                $('#error-msg').hide();

                const formData = $(this).serialize();

                $.post(API_BASE + '/create', formData)
                    .done(function (res) {
                        if (res.success && res.data && res.data.order) {
                            renderSummary(res.data.order.id);
                        } else {
                            btn.prop('disabled', false).text(originalText);
                            $('#error-msg').text(res.message || 'Failed to create order').show();
                        }
                    })
                    .fail(function (err) {
                        btn.prop('disabled', false).text(originalText);
                        const msg = err.responseJSON ? err.responseJSON.message : 'An error occurred';
                        $('#error-msg').text(msg).show();
                    });
            });
        };

        const renderSummary = (orderId) => {
            // Fetch Order Details including keys
            $.get(API_BASE + '/order/' + orderId)
                .done(function (res) {
                    if (!res.success || !res.data) {
                        alert('Order not found');
                        renderForm();
                        return;
                    }
                    const order = res.data.order;
                    const rzpKey = res.data.rzp_key;
                    const stripeKey = res.data.stripe_key;

                    if (order.payment_status === 'completed') {
                        renderSuccess(order);
                        return;
                    }

                    const html = `
                    <div class="card fade-in">
                        <div class="card-header">
                            <h1>Order Summary</h1>
                            <p>Review and Pay</p>
                        </div>
                        <div class="card-body">
                            <div class="summary-group">
                                <div class="summary-row"><span class="summary-label">Name</span><span class="summary-value">${order.name}</span></div>
                                <div class="summary-row"><span class="summary-label">Email</span><span class="summary-value">${order.email}</span></div>
                                <div class="summary-row"><span class="summary-label">Mobile</span><span class="summary-value">${order.mobile}</span></div>
                                <div class="summary-row"><span class="summary-label">Address</span><span class="summary-value">${order.city}, ${order.country}</span></div>
                                <div class="summary-row"><span class="summary-label">Gateway</span><span class="summary-value">${order.gateway}</span></div>
                            </div>
                            
                            <div class="total-row">
                                <span>Total Amount</span>
                                <span>${order.currency} ${parseFloat(order.amount).toFixed(2)}</span>
                            </div>

                            <button id="payBtn" class="btn" style="margin-top: 2rem;">Pay Now</button>
                            <button id="backBtn" class="btn" style="margin-top: 0.5rem; background: #9ca3af;">Back</button>
                        </div>
                        <div id="stripe-element-container" style="padding: 1rem; display: none;">
                            <div id="payment-element"></div>
                            <button id="submitStripe" class="btn" style="margin-top: 1rem;">Process Payment</button>
                        </div>
                    </div>
                `;
                    $('#payment-app').html(html);

                    $('#backBtn').click(renderForm);
                    $('#payBtn').click(function () {
                        const btn = $(this);
                        btn.prop('disabled', true).html('<div class="loader"></div>');

                        if (order.gateway === 'Razorpay') {
                            initRazorpay(orderId, rzpKey);
                        } else if (order.gateway === 'Stripe') {
                            initStripe(orderId, stripeKey);
                        }
                    });
                });
        };

        const initRazorpay = (orderId, key) => {
            $.post(API_BASE + '/prepare', { order_id: orderId })
                .done(function (res) {
                    if (!res.success || !res.data) { alert(res.message || 'Failed to prepare payment'); return; }
                    const data = res.data;

                    var options = {
                        "key": key,
                        "amount": data.amount,
                        "currency": data.currency,
                        "name": "Open Payment",
                        "description": data.description,
                        "image": "https://cdn-icons-png.flaticon.com/512/2111/2111615.png", // Placeholder
                        "order_id": data.razorpay_order,
                        "handler": function (response) {
                            verifyRazorpay(orderId, response);
                        },
                        "prefill": {
                            "name": data.name,
                            "email": data.email,
                            "contact": data.mobile
                        },
                        "theme": { "color": "#6366f1" }
                    };
                    var rzp1 = new Razorpay(options);
                    rzp1.on('payment.failed', function (response) {
                        renderFailed(response.error.description);
                    });
                    rzp1.open();
                    $('#payBtn').prop('disabled', false).text('Pay Now');
                })
                .fail(function () {
                    alert('Failed to initialize Payment');
                    $('#payBtn').prop('disabled', false).text('Pay Now');
                });
        };

        const verifyRazorpay = (orderId, response) => {
            $.post(API_BASE + '/verify/razorpay', {
                order_id: orderId,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature
            }).done(function (res) {
                if (res.success && res.data && res.data.order) { renderSuccess(res.data.order); }
                else { renderFailed(res.message || 'Payment verification failed'); }
            }).fail(function (err) {
                renderFailed("Verification failed");
            });
        };

        const initStripe = (orderId, key) => {
            const stripe = Stripe(key);
            const elements = stripe.elements();
            const cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#424770',
                        '::placeholder': { color: '#aab7c4' },
                    },
                    invalid: { color: '#9e2146' },
                }
            });

            $('#payBtn').hide();
            $('#backBtn').hide();
            $('#stripe-element-container').show();
            $('#payment-element').empty();
            cardElement.mount('#payment-element');

            $('#submitStripe').off('click').on('click', async function () {
                const btn = $(this);
                btn.prop('disabled', true).text('Processing...');

                // Create token from card element
                const { token, error } = await stripe.createToken(cardElement);

                if (error) {
                    btn.prop('disabled', false).text('Process Payment');
                    renderFailed(error.message);
                    return;
                }

                // Send token to backend for payment processing
                $.post(API_BASE + '/prepare', {
                    order_id: orderId,
                    stripeToken: token.id
                }).done(function (res) {
                    if (res.success && res.data) {
                        const data = res.data;
                        if (data.status === 'succeeded') {
                            // Payment successful
                            $.get(API_BASE + '/order/' + orderId).done(function (orderRes) {
                                if (orderRes.success && orderRes.data && orderRes.data.order) {
                                    renderSuccess(orderRes.data.order);
                                } else {
                                    renderSuccess({ id: orderId, payment_status: 'completed' });
                                }
                            });
                        } else if (data.status === 'requires_action' && data.redirect_url) {
                            // 3D Secure required - redirect
                            window.location.href = data.redirect_url;
                        } else {
                            renderFailed('Payment status: ' + data.status);
                        }
                    } else {
                        btn.prop('disabled', false).text('Process Payment');
                        renderFailed(res.message || 'Payment failed');
                    }
                }).fail(function (err) {
                    btn.prop('disabled', false).text('Process Payment');
                    const msg = err.responseJSON ? err.responseJSON.message : 'Payment failed';
                    renderFailed(msg);
                });
            });
        };

        const renderSuccess = (order) => {
            const html = `
            <div class="card fade-in">
                <div class="card-body" style="text-align: center;">
                    <div class="status-icon success-icon">✓</div>
                    <h2 class="status-title">Payment Successful!</h2>
                    <p class="status-message">Thank you for your payment. Your transaction has been completed successfully.</p>
                    
                    <div style="margin-bottom: 0.5rem; color: #6b7280; font-size: 0.9rem;">Transaction ID</div>
                    <div class="ref-id">${order.transaction_id || order.id}</div>
                    
                    <div class="summary-group" style="text-align: left; margin-bottom: 2rem;">
                        <div class="summary-row"><span class="summary-label">Amount Paid</span><span class="summary-value">${order.currency} ${parseFloat(order.amount).toFixed(2)}</span></div>
                        <div class="summary-row"><span class="summary-label">Date</span><span class="summary-value">${new Date().toLocaleDateString()}</span></div>
                    </div>

                    <button onclick="renderForm()" class="btn">Make Another Payment</button>
                </div>
            </div>
        `;
            $('#payment-app').html(html);
        };

        const renderFailed = (msg) => {
            const html = `
            <div class="card fade-in">
                <div class="card-body" style="text-align: center;">
                    <div class="status-icon failed-icon">✕</div>
                    <h2 class="status-title">Payment Failed</h2>
                    <p class="status-message">${msg || 'Something went wrong. Please try again.'}</p>
                    <button onclick="renderForm()" class="btn">Try Again</button>
                </div>
            </div>
        `;
            $('#payment-app').html(html);
        };

        // Initialize
        $(document).ready(function () {
            // Check for redirect parameters (from Stripe 3D Secure)
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const orderId = urlParams.get('order_id');
            const message = urlParams.get('message');

            if (status === 'success' && orderId) {
                // Fetch order and show success
                $.get(API_BASE + '/order/' + orderId).done(function (res) {
                    if (res.success && res.data && res.data.order) {
                        renderSuccess(res.data.order);
                    } else {
                        renderSuccess({ id: orderId, payment_status: 'completed' });
                    }
                }).fail(function () {
                    renderSuccess({ id: orderId, payment_status: 'completed' });
                });
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (status === 'failed' || status === 'error') {
                renderFailed(message ? decodeURIComponent(message) : 'Payment failed');
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (status === 'pending') {
                renderFailed(message ? decodeURIComponent(message) : 'Payment is still processing. Please check back later.');
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            } else {
                renderForm();
            }
        });
    </script>

</body>

</html>
