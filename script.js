const openSaleBtn = document.querySelector('.openSaleForm');
const saleWindow = document.querySelector('.sale-window');
const windowWrapper = saleWindow.parentElement;
const closeWindowBtn = saleWindow.querySelector('.close-window')
// Открытие окна регистраций на акцию
openSaleBtn.addEventListener('click', () => {
    windowWrapper.classList.add('active');
    saleWindow.classList.add('active');
});
closeWindowBtn.addEventListener("click", () => {
    windowWrapper.classList.remove('active');
    saleWindow.classList.remove('active');
})
// Закрытие окна при нажатий вне окна
windowWrapper.addEventListener('click', (event) => {
    if (event.target === windowWrapper) {
        windowWrapper.classList.remove('active');
        saleWindow.classList.remove('active');
    }
});


document.querySelector('.sale-form').addEventListener('submit', async (e) => {
    // Сбрасываем дефолтный обработчик
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    const response = await fetch('admin/submit_form.php', {
        method: 'POST',
        body: formData
    });
    console.log(response)
    const result = await response.json();
    console.log(result)
    // Удаляем старые уведомления
    document.querySelectorAll('.popup-message').forEach(el => el.remove());

    // Создание поп-ап окна в правом верхнем углу для вывода информаций
    const popup = document.createElement('div');
    popup.className = `popup-message ${result.status}`;
    popup.textContent = result.message;
    document.body.appendChild(popup);

    // Проверка результата на успешность и автоматическое закрытие формы со сбросом данных
    if (result.status === "success") {
        form.reset();
        setTimeout(() => {
            document.querySelector('.sale-window').classList.remove('active');
            document.querySelector('.sale-window').parentElement.classList.remove('active');
        }, 1000);
    }
    // Авто изчесзновение поп-ап окна спустя 3с
    setTimeout(() => popup.remove(), 3000);
});
