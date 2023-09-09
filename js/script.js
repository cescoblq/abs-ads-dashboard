document.addEventListener('DOMContentLoaded', (event) => {
	
	
	// ------------------------------------------------------------
	//  Fonction pour accordeons / collapse sur les sections Campagnes
	// ------------------------------------------------------------
	document.body.addEventListener('click', (event) => {		
		/*if (event.target.matches('.toggle-button')) {
            const section = event.target.closest('.adcampaign-section');
            section.classList.toggle('collapsed');
            event.target.textContent = section.classList.contains('collapsed') ? '►' : '▼';
        }*/
		
		
      /*  if (event.target.matches('.toggle-button')) {
            const section = event.target.closest('.adcampaign-section');
            const table = section.querySelector('.adsets-table');
            const footer = section.querySelector('.budgetcampaign');
            table.classList.toggle('hidden');
            footer.classList.toggle('hidden');
            event.target.textContent = table.classList.contains('hidden') ? '►' : '▼';
        }*/
		
		if (event.target.matches('.toggle-button')) {
			const section = event.target.closest('.adcampaign-section, .adaccount');
			const elementsToToggle = section.classList.contains('adaccount') 
				? section.querySelectorAll('.adcampaign-section') 
				: [section.querySelector('.adsets-table'), section.querySelector('.budgetcampaign')];
			elementsToToggle.forEach(el => el.classList.toggle('hidden'));
			event.target.textContent = elementsToToggle[0].classList.contains('hidden') ? '►' : '▼';
		}
		
 
	});

   



	// ------------------------------------------------------------
	// Fonction moteur de recherche / filtre du DOM par keyword
	// ------------------------------------------------------------
    document.getElementById('filter-button').addEventListener('click', () => {
        const keyword = document.getElementById('keyword-input').value.toLowerCase();
        const sections = document.querySelectorAll('.adcampaign-section');

        sections.forEach(section => {
            const title = section.querySelector('.adcampaign').textContent.toLowerCase();
            if (keyword && !title.includes(keyword)) {
                section.style.display = 'none';
            } else {
                section.style.display = 'block';
            }
        });
    });
});

