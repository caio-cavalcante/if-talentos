document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-bookmark').forEach(button => {
        button.addEventListener('click', function() {
            const vagaId = this.getAttribute('data-vaga-id');
            const icon = this.querySelector('i');
            
            fetch('/api/salvar_vaga.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_vaga: vagaId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.classList.toggle('saved');
                    if (data.action === 'saved') {
                        icon.classList.remove('fa-regular');
                        icon.classList.add('fa-solid');
                        this.setAttribute('title', 'Remover dos salvos');
                    } else {
                        icon.classList.remove('fa-solid');
                        icon.classList.add('fa-regular');
                        this.setAttribute('title', 'Salvar vaga');
                    }
                } else {
                    alert(data.message || 'Ocorreu um erro.');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});