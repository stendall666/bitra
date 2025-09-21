document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.match(/\/company\/personal\/user\/\d+\/tasks\/$/)) {
        var pagetitleContainer = document.querySelector('.pagetitle');
        
        if (pagetitleContainer) {
            var subordinatesButton = document.createElement('a');
            subordinatesButton.href = '/test_subordinates.php';
            subordinatesButton.className = 'ui-btn ui-btn-primary ui-btn-sm';
            subordinatesButton.style.marginLeft = '15px';
            subordinatesButton.innerHTML = 'Задачи подчиненных';
            
            
            pagetitleContainer.parentNode.insertBefore(subordinatesButton, pagetitleContainer.nextSibling);
        }
    }
});