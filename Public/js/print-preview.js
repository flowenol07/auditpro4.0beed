const printBtn = document.getElementById('printBtn');
const printContent = document.getElementById('printContainer')

if(printBtn !== null && printContent !== null)
{
    printBtn.addEventListener('click', function() {

        fetch( printBtn.getAttribute('data-url'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
            body: 'report=' + reportName + '&printPage=' + pagePrint + '&content=' + encodeURIComponent(printContent.innerHTML),
        }).then(response => response.text()).then(html => {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(html);
            printWindow.document.close();
        });

    });
}