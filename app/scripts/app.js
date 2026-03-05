import { Module } from 'scalar';
import Menu from './components/menu';

let fuse;
let currentFocus = -1;
const searchInput = document.getElementById('docs-search');
const resultsContainer = document.getElementById('search-results');

fetch(`${root}index.json`)
.then(response => response.json())
.then(data => {
    const options = {
        keys: [
            { name: 'title', weight: 0.7 },
            { name: 'content', weight: 0.2 }
        ],
        threshold: 0.2,
        ignoreLocation: true,
        distance: 1000,
        includeScore: true
    };
    fuse = new Fuse(data, options);
});

searchInput.addEventListener('input', (e) => {
    const query = e.target.value;
    currentFocus = -1;
    if (query.length < 2) {
        resultsContainer.style.display = 'none';
        return;
    }
    const results = fuse.search(query);
    displayResults(results);
});

function displayResults(results) {
    if (results.length === 0) {
        resultsContainer.innerHTML = '<div class="no-results">No se encontraron coincidencias</div>';
    } else {
        resultsContainer.innerHTML = results.slice(0, 8).map(result => {
            const item = result.item;
            const url = `${root}docs/${item.file === 'intro' ? '' : item.file + '/'}${item.anchor ? '#' + item.anchor : ''}`;

            return `
                <a href="${url}" class="result-item">
                    <div class="result-header">
                        <span class="result-title">${item.title}</span>
                        <span class="result-badge">${item.file}</span>
                    </div>
                    <span class="result-preview">${item.content.substring(0, 100)}...</span>
                </a>
            `;
        }).join('');
    }
    resultsContainer.style.display = 'block';
}

document.addEventListener('click', (e) => {
    resultsContainer.style.display = 'none';
    if (e.target.closest('.search-container')) {
        searchInput.value = '';
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement !== searchInput) {
        e.preventDefault();
        searchInput.focus();
        searchInput.select();
    }
});

searchInput.addEventListener('keydown', function(e) {
    let items = resultsContainer.getElementsByClassName('result-item');
    if (e.key === "ArrowDown" && items && items.length !== 0) {
        currentFocus++;
        addActive(items);
    } else if (e.key === "ArrowUp" && items && items.length !== 0) {
        currentFocus--;
        addActive(items);
    } else if (e.key === "Enter") {
        e.preventDefault();
        if (currentFocus > -1) {
            if (items[currentFocus]) {
                items[currentFocus].click();
            }
            resultsContainer.style.display = 'none';
            searchInput.value = '';
        }
    } else if (e.key === "Escape") {
        resultsContainer.style.display = 'none';
        searchInput.blur();
    }
});

function addActive(items) {
    removeActive(items);
    if (currentFocus >= items.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = items.length - 1;
    items[currentFocus].classList.add("active");
    items[currentFocus].scrollIntoView({ block: 'nearest' });
}

function removeActive(items) {
    for (let i = 0; i < items.length; i++) {
        items[i].classList.remove("active");
    }
}

new Module()
.compose('#main-docs', Menu)
.execute();
