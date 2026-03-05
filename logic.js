function openModal(tariffName) {
    document.getElementById('modalForm').style.display = 'block';
    document.getElementById('hiddenTariff').value = tariffName;
}

// Поиск адреса (15 пункт)
async function checkAddress() {
    let query = document.getElementById('addressInput').value;
    let response = await fetch(`check_address.php?q=${query}`);
    let data = await response.json();
    
    let resultDiv = document.getElementById('addressResult');
    if(data.found) {
        resultDiv.innerHTML = data.available ? 
            "<span style='color:green'>По вашему адресу подключение доступно</span>" : 
            "<span style='color:red'>К сожалению, интернет недоступен</span>";
    } else {
        resultDiv.innerText = "Адрес не найден";
    }
}