<div id="global-loader" style="display: none;">
    <div class="fulfilling-bouncing-circle-spinner">
        <div class="circle"></div>
        <div class="orbit"></div>
    </div>
</div>

<style>
    .custom-loader-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: transparent;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .fulfilling-bouncing-circle-spinner,
    .fulfilling-bouncing-circle-spinner * {
        box-sizing: border-box;
    }

    .fulfilling-bouncing-circle-spinner {
        height: 60px;
        width: 60px;
        position: relative;
        animation: fulfilling-bouncing-circle-spinner-animation infinite 4000ms ease;
    }

    .fulfilling-bouncing-circle-spinner .orbit {
        height: 60px;
        width: 60px;
        position: absolute;
        top: 0;
        left: 0;
        border-radius: 50%;
        border: calc(60px * 0.03) solid #1d78ff;
        animation: fulfilling-bouncing-circle-spinner-orbit-animation infinite 4000ms ease;
    }

    .fulfilling-bouncing-circle-spinner .circle {
        height: 60px;
        width: 60px;
        color: #1d78ff;
        display: block;
        border-radius: 50%;
        position: relative;
        border: calc(60px * 0.1) solid #1d78ff;
        animation: fulfilling-bouncing-circle-spinner-circle-animation infinite 4000ms ease;
        transform: rotate(0deg) scale(1);
    }

    @keyframes fulfilling-bouncing-circle-spinner-animation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes fulfilling-bouncing-circle-spinner-orbit-animation {
        0%, 50% {
            transform: scale(1);
        }
        62.5%, 87.5% {
            transform: scale(0.8);
        }
        75%, 100% {
            transform: scale(1);
        }
    }

    @keyframes fulfilling-bouncing-circle-spinner-circle-animation {
        0% {
            transform: scale(1);
            border-color: transparent;
            border-top-color: inherit;
        }
        16.7% {
            border-color: transparent;
            border-top-color: initial;
            border-right-color: initial;
        }
        33.4% {
            border-color: transparent;
            border-top-color: inherit;
            border-right-color: inherit;
            border-bottom-color: inherit;
        }
        50% {
            border-color: inherit;
            transform: scale(1);
        }
        62.5% {
            border-color: inherit;
            transform: scale(1.4);
        }
        75% {
            border-color: inherit;
            transform: scale(1);
            opacity: 1;
        }
        87.5% {
            border-color: inherit;
            transform: scale(1.4);
        }
        100% {
            border-color: transparent;
            border-top-color: inherit;
            transform: scale(1);
        }
    }
</style>

<script>
    const globalLoader = {
        show: function (container = document.body) {
            let loader = container.querySelector('.custom-loader-wrapper');

            if (!loader) {
                loader = document.createElement('div');
                loader.className = 'custom-loader-wrapper';
                loader.innerHTML = `
                <div class="fulfilling-bouncing-circle-spinner">
                    <div class="circle"></div>
                    <div class="orbit"></div>
                </div>
            `;
                container.appendChild(loader);
            }

            loader.style.display = 'flex';
        },

        hide: function (container = document.body) {
            const loader = container.querySelector('.custom-loader-wrapper');
            if (loader) {
                loader.style.display = 'none';
            }
        }
    };

</script>