(function () {
    // 初始化通知容器
    function initNotificationContainer() {
        // 如果已存在容器，先移除（防止重复）
        const existing = document.getElementById("notification-container");
        if (existing) existing.remove();

        const style = document.createElement("style");
        style.textContent = `
            #notification-container {
                position: fixed;
                top: 35px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
        
            .notification {
                background-color: #405fff;
                color: white;
                padding: 8px 25px;
                font-size: 15px;
                border-radius: 10px;
                white-space: nowrap;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                animation: fadeInDrop 0.2s ease-out forwards;
            }
        
            .notification.hide {
                animation: fadeOutUp 0.2s ease-in forwards;
            }
        
            @keyframes fadeInDrop {
                0% {
                    opacity: 0;
                    transform: translateY(-20px) scale(0.5);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
        
            @keyframes fadeOutUp {
                0% {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
                100% {
                    opacity: 0;
                    transform: translateY(-20px) scale(0.5);
                }
            }
        `;

        document.head.appendChild(style);

        const container = document.createElement("div");
        container.id = "notification-container";
        document.body.appendChild(container);
    }

    // 等待 DOM 完成再初始化
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initNotificationContainer);
    } else {
        initNotificationContainer();
    }

    // 全局函数：显示一个新的通知
    window.showNotification = function (message, duration = 2200) {
        const container = document.getElementById("notification-container");
        if (!container) return;

        const notification = document.createElement("div");
        notification.className = "notification";
        notification.textContent = message;

        container.appendChild(notification);

        // 强制触发 reflow 以启用动画
        requestAnimationFrame(() => {
            notification.classList.add("show");
        });

        if (duration > 0) {
            setTimeout(() => {
                notification.classList.remove("show");
                setTimeout(() => {
                    notification.remove();
                }, 400);
            }, duration);
        }
    };
})();
