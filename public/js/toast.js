/**
 * @fileoverview Système de notification UI non bloquant (Toast).
 * Génère des alertes visuelles temporaires en utilisant les classes utilitaires de Tailwind CSS.
 */

const ToastProvider = {
    /**
     * Conteneur principal pour les notifications.
     * @type {HTMLElement|null}
     */
    container: null,

    /**
     * Initialise le conteneur de Toasts dans le DOM s'il n'existe pas.
     * 
     * @returns {void}
     */
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-3 pointer-events-none';
            document.body.appendChild(this.container);
        }
    },

    /**
     * Affiche une notification à l'écran.
     * 
     * @param {string} message - Le texte à afficher.
     * @param {('success'|'error'|'info'|'warning')} [type='info'] - Le type de notification définissant la couleur.
     * @param {number} [duration=3000] - La durée d'affichage en millisecondes.
     * @returns {void}
     */
    show(message, type = 'info', duration = 3000) {
        this.init();

        const toast = document.createElement('div');
        
        // Définition des couleurs selon le type
        const colorClasses = {
            success: 'bg-green-600 text-white',
            error: 'bg-red-600 text-white',
            warning: 'bg-orange-500 text-white',
            info: 'bg-blue-600 text-white'
        };
        const bgClass = colorClasses[type] || colorClasses['info'];

        // Styling Tailwind de l'élément Toast
        toast.className = `max-w-xs w-full px-4 py-3 rounded shadow-lg pointer-events-auto transform transition-all duration-300 translate-y-10 opacity-0 ${bgClass} flex items-center justify-between`;
        toast.innerHTML = `<span class="text-sm font-medium">${message}</span>`;

        this.container.appendChild(toast);

        // Animation d'entrée
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-10', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });

        // Destruction automatique après délai
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300); // Délai correspondant à la durée de la transition CSS
        }, duration);
    }
};

// Exposition globale pour utilisation dans les autres scripts
window.Toast = ToastProvider;