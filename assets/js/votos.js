// assets/js/votos.js

document.addEventListener('DOMContentLoaded', function() {
    
    const voteButtons = document.querySelectorAll('.btn-voto');

    voteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Evitar doble click si ya está deshabilitado
            if (this.disabled) return;

            const obraId = this.dataset.id;
            const tipo = this.dataset.tipo; // 'like' o 'dislike'
            const contadorSpan = this.querySelector('span'); // El numerito dentro del botón

            // Feedback visual inmediato (UX)
            this.style.opacity = '0.5';
            this.style.cursor = 'wait';

            // Llamada a la API
            fetch(museoData.root_url + '/wp-json/museo/v1/votar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // 'X-WP-Nonce': museoData.nonce // Si requiriéramos nonce
                },
                body: JSON.stringify({
                    id: obraId,
                    tipo: tipo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar número
                    contadorSpan.textContent = data.new_count;
                    
                    // Bloquear ambos botones
                    bloquearBotones(obraId);
                    
                    alert('¡Gracias por tu voto!');
                } else {
                    // Error (ej: ya votó)
                    alert(data.message || 'Error al procesar voto');
                    this.style.opacity = '1';
                    this.style.cursor = 'pointer';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error de conexión');
            });
        });
    });

    function bloquearBotones(id) {
        const botones = document.querySelectorAll(`.btn-voto[data-id="${id}"]`);
        botones.forEach(btn => {
            btn.disabled = true;
            btn.style.background = '#ccc';
            btn.style.cursor = 'not-allowed';
            btn.textContent = (btn.dataset.tipo === 'like' ? '👍' : '👎') + ' Votado (' + btn.querySelector('span').textContent + ')';
        });
    }
});