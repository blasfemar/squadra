document.addEventListener('DOMContentLoaded', function() {
    const seoAnalysisForm = document.getElementById('seoAnalysisForm');
    const urlToAnalyzeInput = document.getElementById('urlToAnalyze');
    const resultsContainer = document.getElementById('results-container');
    const seoScoreChartCanvas = document.getElementById('seoScoreChart');
    let seoScoreChartInstance = null;

    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function createAlert(message, type = 'warning') {
        let bgColor, textColor, borderColor, iconSvg;
        if (type === 'critical') {
            bgColor = 'bg-red-100'; textColor = 'text-red-700'; borderColor = 'border-red-300';
            iconSvg = `<svg class="w-5 h-5 inline mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 102 0V5zm-1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>`;
        } else { // warning
            bgColor = 'bg-yellow-100'; textColor = 'text-yellow-700'; borderColor = 'border-yellow-300';
            iconSvg = `<svg class="w-5 h-5 inline mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.216 3.001-1.742 3.001H4.42c-1.526 0-2.492-1.667-1.742-3.001l5.58-9.92zM10 13a1 1 0 100-2 1 1 0 000 2zm-1-3a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>`;
        }
        return `<div class="${bgColor} ${textColor} p-3 rounded-md border ${borderColor} text-sm mb-3 flex items-start">${iconSvg}<div>${message}</div></div>`;
    }

    function createCard(title, contentHtml) {
        const card = document.createElement('div');
        card.className = 'bg-white p-6 rounded-lg shadow-lg mb-6';

        const titleEl = document.createElement('h3');
        titleEl.className = 'text-xl font-semibold text-gray-700 mb-4 border-b pb-2';
        titleEl.textContent = title;
        card.appendChild(titleEl);

        const contentDiv = document.createElement('div');
        contentDiv.className = 'prose prose-sm max-w-none';
        contentDiv.innerHTML = contentHtml;
        card.appendChild(contentDiv);

        return card;
    }

    function renderSeoScoreChart(score) {
        if (!seoScoreChartCanvas) {
            console.warn('seoScoreChart canvas element not found.');
            // Ensure the canvas container in index.php is not hidden if chart fails
            const chartContainer = document.querySelector('#results-container > div:has(#seoScoreChart)');
            if(chartContainer) chartContainer.style.display = 'block'; // Make sure it's visible to show error or fallback
            return;
        }
         // Make sure the parent div of the canvas is visible
        const chartParentDiv = seoScoreChartCanvas.closest('.bg-white.p-6.rounded-xl.shadow-xl');
        if (chartParentDiv) {
            chartParentDiv.style.display = 'block';
        }


        if (seoScoreChartInstance) {
            seoScoreChartInstance.destroy();
        }

        const scoreValue = parseInt(score, 10) || 0;
        const remainingValue = Math.max(0, 100 - scoreValue);

        let scoreColor = '#4CAF50'; // Green
        if (scoreValue < 50) scoreColor = '#F44336'; // Red
        else if (scoreValue < 80) scoreColor = '#FFC107'; // Yellow

        const data = {
            labels: ['SEO Score', 'Remaining'],
            datasets: [{
                data: [scoreValue, remainingValue],
                backgroundColor: [scoreColor, '#E0E0E0'],
                borderColor: [scoreColor, '#BDBDBD'],
                borderWidth: 1,
                hoverOffset: 4
            }]
        };

        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            },
            plugins: [{
                id: 'doughnutText',
                afterDraw: (chart) => {
                    if (chart.config.type !== 'doughnut') return;
                    const ctx = chart.ctx;
                    const width = chart.width;
                    const height = chart.height;

                    ctx.restore();
                    const fontSize = (height / 114).toFixed(2);
                    ctx.font = fontSize + "em Arial";
                    ctx.textBaseline = "middle";
                    ctx.fillStyle = scoreColor;

                    const text = scoreValue + "%";
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;

                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        };

        try {
            seoScoreChartInstance = new Chart(seoScoreChartCanvas, config);
        } catch (e) {
            console.error("Error creating chart:", e);
            const ctx = seoScoreChartCanvas.getContext('2d');
            ctx.clearRect(0,0,seoScoreChartCanvas.width, seoScoreChartCanvas.height);
            ctx.fillStyle = 'red';
            ctx.textAlign = 'center';
            ctx.fillText('Error loading chart', seoScoreChartCanvas.width/2, seoScoreChartCanvas.height/2);
        }
    }

    function renderAnalysisResults(data) {
        resultsContainer.innerHTML = '';

        // SEO Score Chart (Render first or ensure its container is part of the results flow)
        // The canvas is already in index.php. We just need to make sure its container div is displayed.
        const chartParentDiv = seoScoreChartCanvas ? seoScoreChartCanvas.closest('.bg-white.p-6.rounded-xl.shadow-xl') : null;
        if (chartParentDiv) { // If the chart container from index.php exists
            chartParentDiv.style.display = 'block'; // Make it visible
        }
         if (data.seoScore !== undefined) {
            renderSeoScoreChart(data.seoScore);
        } else {
            if (seoScoreChartInstance) seoScoreChartInstance.destroy();
            seoScoreChartInstance = null;
            if(seoScoreChartCanvas) {
                const ctx = seoScoreChartCanvas.getContext('2d');
                ctx.clearRect(0,0,seoScoreChartCanvas.width, seoScoreChartCanvas.height);
                 if (chartParentDiv) chartParentDiv.style.display = 'none'; // Hide if no score
            }
        }


        let metaContent = '';
        if (data.title && data.title.text !== undefined) {
            metaContent += `<p><strong>Title:</strong> ${escapeHTML(data.title.text)} (Length: ${data.title.length} chars)</p>`;
            if (data.title.length === 0) metaContent += createAlert('Page title is missing.', 'critical');
            else if (data.title.length < 10 || data.title.length > 70) metaContent += createAlert('Title length is suboptimal (ideal: 10-70 chars).', 'warning');
        } else {
            metaContent += createAlert('Page title data is missing or incomplete.', 'critical');
        }
        if (data.metaDescription && data.metaDescription.text !== undefined) {
            metaContent += `<p class="mt-2"><strong>Meta Description:</strong> ${escapeHTML(data.metaDescription.text)} (Length: ${data.metaDescription.length} chars)</p>`;
            if (data.metaDescription.length === 0) metaContent += createAlert('Meta description is missing.', 'warning');
            else if (data.metaDescription.length < 70 || data.metaDescription.length > 160) metaContent += createAlert('Meta description length is suboptimal (ideal: 70-160 chars).', 'warning');
        } else {
            metaContent += createAlert('Meta description data is missing.', 'warning');
        }
        resultsContainer.appendChild(createCard('Meta Tags', metaContent));

        let headingsContent = '';
        if (data.headings) {
            headingsContent += `<p><strong>H1 Count:</strong> ${data.headings.h1Count}</p>`;
            if (data.headings.h1Count === 0) headingsContent += createAlert('No H1 tag found on the page.', 'critical');
            else if (data.headings.h1Count > 1) headingsContent += createAlert('Multiple H1 tags found. Ideally, there should be only one.', 'warning');

            if (data.headings.allHeadings && data.headings.allHeadings.length > 0) {
                headingsContent += '<p class="mt-2"><strong>All Headings (H1-H6):</strong></p><ul class="list-disc list-inside mt-1">';
                data.headings.allHeadings.forEach(h => {
                    headingsContent += `<li>${escapeHTML(h)}</li>`;
                });
                headingsContent += '</ul>';
            } else {
                headingsContent += '<p class="mt-2">No headings (H1-H6) found.</p>';
            }
        }
        resultsContainer.appendChild(createCard('Headings Analysis', headingsContent));

        let imagesContent = '';
        if (data.images) {
            imagesContent += `<p><strong>Total Images:</strong> ${data.images.total}</p>`;
            imagesContent += `<p><strong>Images Missing Alt Text:</strong> ${data.images.missingAlt}</p>`;
            if (data.images.missingAlt > 0) {
                imagesContent += createAlert(`${data.images.missingAlt} image(s) are missing alt attributes. This is important for accessibility and SEO.`, 'warning');
            }
        }
        resultsContainer.appendChild(createCard('Image Analysis', imagesContent));

        let wordCountContent = '';
        if (data.wordCount !== undefined) {
            wordCountContent += `<p><strong>Approximate Word Count (Body):</strong> ${data.wordCount}</p>`;
            if (data.wordCount < 300 && data.wordCount > 0) {
                 wordCountContent += createAlert('The word count is relatively low (under 300 words). Consider adding more relevant content if appropriate for the page''s purpose.', 'warning');
            } else if (data.wordCount === 0 && data.title && data.title.text) {
                 wordCountContent += createAlert('The page has a title but no significant textual content was found in the body.', 'warning');
            }
        }
        resultsContainer.appendChild(createCard('Content Analysis', wordCountContent));
    }

    function renderError(errorMessage) {
        resultsContainer.innerHTML = `
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg shadow-md" role="alert">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline">${escapeHTML(errorMessage)}</span>
            </div>`;
    }

    if (seoAnalysisForm && urlToAnalyzeInput && resultsContainer) {
        seoAnalysisForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const url = urlToAnalyzeInput.value.trim();
            if (!url) {
                renderError('Please enter a URL to analyze.');
                return;
            }

            resultsContainer.innerHTML = `
                <div class="text-center p-6 bg-white rounded-lg shadow-md">
                    <p class="text-lg font-semibold text-blue-600">Analyzing, please wait...</p> {/* TODO: Translate */}
                    <div class="mt-4">
                        <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>`;

            const formData = new FormData();
            formData.append('url', url);

            fetch('api/analyze.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => {
                        throw new Error(errData.error || `Network response was not ok: ${response.statusText} (Status: ${response.status})`);
                    }).catch(() => {
                        throw new Error(`Network response was not ok: ${response.statusText} (Status: ${response.status})`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderAnalysisResults(data.data);
                } else {
                    renderError(data.error || 'An unknown error occurred during analysis.');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                renderError(`Fetch Error: ${error.message}`);
            });
        });
    } else {
        console.warn('Required form elements (seoAnalysisForm, urlToAnalyze, resultsContainer, seoScoreChartCanvas) not found or seoScoreChartCanvas is missing.');
    }
});
