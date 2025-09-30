<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define o título da página e inclui o header
$pageTitle = "Sobre | IF - Talentos";
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <main class="about-page">
        <div class="container">

            <section class="about-section about-intro">
                <h2>Nosso Desafio, Nossa Missão</h2>
                <p>
                    Identificamos uma lacuna crítica: a dificuldade que alunos do curso de Bacharelado em Sistemas de Informação (BSI) do IFBA-FSA enfrentam para encontrar estágios obrigatórios de qualidade. Ao mesmo tempo, empresas da região buscam por talentos qualificados, mas nem sempre sabem onde encontrá-los.
                </p>
                <p class="mission-statement">
                    <strong>Nossa missão é criar a ponte entre os talentos em formação no curso de BSI do IFBA e as empresas que inovam na região, facilitando o cumprimento dos estágios obrigatórios e impulsionando a empregabilidade local.</strong>
                </p>
            </section>

            <section class="about-section platform-what">
                <h2>O Que é o Banco de Talentos?</h2>
                <p>
                    Esta plataforma é uma iniciativa institucional projetada para ser mais do que um simples repositório de currículos. É um ecossistema dinâmico onde os perfis acadêmicos e técnicos dos nossos alunos ficam visíveis para empresas parceiras, permitindo uma conexão direta e eficiente entre competências específicas e oportunidades de mercado.
                </p>
            </section>

            <section class="about-section how-it-works-section">
                <h2>Como Funciona</h2>
                <div class="how-it-works-grid">
                    <div class="how-it-works-column">
                        <h3>Para Alunos</h3>
                        <ol>
                            <li><strong>Cadastre-se:</strong> Crie seu perfil acadêmico e profissional detalhado.</li>
                            <li><strong>Mostre seu Potencial:</strong> Adicione suas competências, tecnologias e projetos de portfólio.</li>
                            <li><strong>Encontre Oportunidades:</strong> Navegue pelas vagas e candidate-se de forma simplificada.</li>
                        </ol>
                    </div>
                    <div class="how-it-works-column">
                        <h3>Para Empresas</h3>
                        <ol>
                            <li><strong>Seja um Parceiro:</strong> Cadastre sua empresa e aguarde a aprovação da nossa coordenação.</li>
                            <li><strong>Publique suas Vagas:</strong> Descreva as oportunidades de estágio e os perfis que você procura.</li>
                            <li><strong>Encontre o Talento Certo:</strong> Utilize nossa busca para encontrar alunos com as habilidades ideais para sua equipe.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <section class="about-section benefits-section">
                <h2>Benefícios</h2>
                <div class="benefits-grid">
                    <div class="benefits-column">
                        <h4>Vantagens para Alunos</h4>
                        <ul>
                            <li>✅ <strong>Visibilidade Estratégica:</strong> Seu perfil acessível diretamente para recrutadores de empresas parceiras.</li>
                            <li>✅ <strong>Oportunidades Reais:</strong> Acesso a vagas de estágio alinhadas com as exigências do curso de BSI.</li>
                            <li>✅ <strong>Porta de Entrada para o Mercado:</strong> Acelere seu desenvolvimento profissional e inicie sua carreira.</li>
                        </ul>
                    </div>
                    <div class="benefits-column">
                        <h4>Vantagens para Empresas</h4>
                        <ul>
                            <li>✅ <strong>Acesso a Talentos Qualificados:</strong> Encontre alunos com conhecimento técnico atualizado e prontos para contribuir.</li>
                            <li>✅ <strong>Otimização do Recrutamento:</strong> Reduza o tempo e o custo do seu processo seletivo.</li>
                            <li>✅ <strong>Parceria Institucional:</strong> Fortaleça sua marca junto a uma instituição federal e participe da formação de futuros profissionais.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="about-section who-we-are">
                <h2>Quem Somos</h2>
                <p>Este projeto é uma iniciativa acadêmica desenvolvida no âmbito das disciplinas de Processo de Desenvolvimento de Software, Programação Web e Banco de Dados 2, do curso de Bacharelado em Sistemas de Informação do Instituto Federal da Bahia (IFBA) - Campus Feira de Santana.</p>

                <div class="slideshow-container">
                    <div class="slideshow-wrapper">
                        <!-- Slide 1 -->
                        <div class="slide">
                            <img src="/assets/images/Breno.jpeg" alt="Foto de Breno Santana de Souza">
                            <div class="slide-caption">Breno Santana de Souza</div>
                        </div>
                        <!-- Slide 2 -->
                        <div class="slide">
                            <img src="/assets/images/Caio.jpeg" alt="Foto de Caio Cavalcante Araújo">
                            <div class="slide-caption">Caio Cavalcante Araújo</div>
                        </div>
                        <!-- Slide 3 -->
                        <div class="slide">
                            <img src="/assets/images/Daniel.jpeg" alt="Foto de Daniel de Souza Pereira">
                            <div class="slide-caption">Daniel de Souza Pereira</div>
                        </div>
                    </div>

                    <!-- Botões de Navegação -->
                    <button class="slide-btn prev">&#10094;</button>
                    <button class="slide-btn next">&#10095;</button>

                    <!-- Pontos de Navegação -->
                    <div class="slide-dots">
                        <span class="dot active" data-slide-to="0"></span>
                        <span class="dot" data-slide-to="1"></span>
                        <span class="dot" data-slide-to="2"></span>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.querySelector('.slideshow-wrapper');
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.dot');
            const prevBtn = document.querySelector('.slide-btn.prev');
            const nextBtn = document.querySelector('.slide-btn.next');

            let currentIndex = 0;
            const totalSlides = slides.length;

            function showSlide(index) {
                // Move o wrapper para a posição correta
                wrapper.style.transform = `translateX(-${index * 100}%)`;

                // Atualiza a classe 'active' nos pontos de navegação
                dots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('active', dotIndex === index);
                });

                currentIndex = index;
            }

            // Event listeners para os botões de próximo/anterior
            nextBtn.addEventListener('click', () => {
                const nextIndex = (currentIndex + 1) % totalSlides;
                showSlide(nextIndex);
            });

            prevBtn.addEventListener('click', () => {
                const prevIndex = (currentIndex - 1 + totalSlides) % totalSlides;
                showSlide(prevIndex);
            });

            // Event listeners para os pontos de navegação
            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    const slideIndex = parseInt(dot.getAttribute('data-slide-to'));
                    showSlide(slideIndex);
                });
            });

            // Inicia no primeiro slide
            showSlide(0);
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>