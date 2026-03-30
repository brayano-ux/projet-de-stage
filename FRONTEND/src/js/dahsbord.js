 // Theme Toggle Function
        function toggleTheme() {
            const body = document.body;
            const themeIcon = document.getElementById('themeIcon');
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update icon
            if (newTheme === 'light') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
            
            showNotification(newTheme === 'light' ? '☀️ Mode clair activé' : '🌙 Mode sombre activé');
        }

        // Load saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const themeIcon = document.getElementById('themeIcon');
            
            document.body.setAttribute('data-theme', savedTheme);
            
            if (savedTheme === 'light') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
        });

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Prevent body scroll when sidebar is open
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        // Close sidebar
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Upgrade to premium
        function upgradePremium() {
            const whatsapp = `https://wa.me/237657300644?text=Bonjour, je suis intéressé par votre offre premium !`;
            window.open(whatsapp, '_blank');
        }

        // Share boutique
        function shareBoutique() {
            if (navigator.share) {
                navigator.share({
                    title: 'Ma Boutique - Creator Market',
                    text: 'Découvrez ma boutique sur Creator Market !',
                    url: window.location.href
                }).catch(err => {
                    console.log('Erreur lors du partage:', err);
                    copyToClipboard();
                });
            } else {
                copyToClipboard();
            }
        }

        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                showNotification('📋 Lien copié dans le presse-papier !');
            });
        }

        // Show notification
        function showNotification(message) {
            // Créer une notification toast simple
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                color: white;
                padding: 16px 24px;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                z-index: 10000;
                font-weight: 600;
                animation: slideInUp 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutDown 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Open WhatsApp
        function openWhatsApp() {
            const whatsapp = `https://wa.me/237657300644?text=Bonjour, j'ai une question concernant ma boutique.`;
            window.open(whatsapp, '_blank');
        }

        // Toggle language
        function toggleLanguage() {
            showNotification('🌍 Changement de langue - Fonctionnalité à venir');
        }

        // Open notifications
        function openNotifications() {
            showNotification('🔔 Notifications - Fonctionnalité à venir');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 1024 && 
                !sidebar.contains(event.target) && 
                !mobileToggle.contains(event.target) && 
                sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 1024) {
                    closeSidebar();
                }
            }, 250);
        });

        // Animate progress bars on load
        window.addEventListener('load', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach((bar, index) => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100 + (index * 100));
            });
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(event) {
            // Escape key closes sidebar
            if (event.key === 'Escape') {
                closeSidebar();
            }
            // Ctrl/Cmd + K toggles theme
            if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
                event.preventDefault();
                toggleTheme();
            }
        });

        // Add CSS for toast animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(100%);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes slideOutDown {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateY(100%);
                }
            }
        `;
        document.head.appendChild(style);