let isRequestInProgress = false;

document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    if (isRequestInProgress) {
        alert('Por favor, espera a que se procese la solicitud anterior.');
        return;
    }

    isRequestInProgress = true;

    const userInput = document.getElementById('user-input').value;
    const chatBox = document.getElementById('chat-box');

    // Mostrar el mensaje del usuario
    const userMessage = document.createElement('div');
    userMessage.className = 'user';
    userMessage.textContent = userInput;
    chatBox.appendChild(userMessage);

    // Limpiar el input
    document.getElementById('user-input').value = '';

    try {
        // Enviar el mensaje al backend
        const response = await fetch('chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: userInput })
        });

        const data = await response.json();

        // Mostrar la respuesta del bot
        const botMessage = document.createElement('div');
        botMessage.className = 'bot';
        botMessage.textContent = data.reply;
        chatBox.appendChild(botMessage);

        // Desplazar hacia abajo
        chatBox.scrollTop = chatBox.scrollHeight;
    } catch (error) {
        console.error('Error:', error);
        alert('Hubo un problema al procesar tu solicitud.');
    } finally {
        // Agregar un retraso antes de permitir otra solicitud
        setTimeout(() => {
            isRequestInProgress = false;
        }, 2000); // 2 segundos de retraso
    }
});
