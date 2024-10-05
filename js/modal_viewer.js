document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');
    const filterButtons = document.querySelectorAll('.filter-btn');

    // Handle active state for navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });

    // Handle active state for filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            filterFiles(button.textContent.trim());
        });
    });

    // Function to filter files (dummy example)
    function filterFiles(filterType) {
        console.log(`Filtering files by: ${filterType}`);
        // Add file filtering logic here
    }

    // Example of a notification system
    const notification = document.querySelector('.notification');

    function showNotification(message) {
        notification.textContent = message;
        notification.classList.add('show');
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    // Example usage
    showNotification('Dashboard v1.0 notes deleted. Restore or dismiss.');
});

// JavaScript function to fetch and display files based on department
function showFilesByDepartment(department, element) {
    // Remove active state from all sidebar items
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    if (element) element.classList.add('active');

    // Send AJAX request to fetch files by department
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `./php/list_files.php?department=${department}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('file-list').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

// On page load, show user's department files by default
window.onload = function() {
    const userDept = document.getElementById('user-department').getAttribute('id');
    document.getElementById('user-department').classList.add('active');
    showFilesByDepartment(userDept, document.getElementById('user-department'));
};

// Function to render all pages of a PDF using pdf.js
function renderPDF(pdfUrl, container) {
    const pdfjsLib = window['pdfjs-dist/build/pdf'];

    // Use the correct worker version
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';

    // Load the PDF document
    pdfjsLib.getDocument(pdfUrl).promise.then(pdfDoc => {
        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
            const canvas = document.createElement('canvas');
            canvas.style.display = 'block';
            container.appendChild(canvas);

            // Render each page
            renderPage(pdfDoc, pageNum, canvas);
        }
    }).catch(error => {
        console.error('Error rendering PDF:', error.message, error);
        container.innerHTML = `<p>Error: Unable to load the PDF file.</p>`;
    });
}

// Function to render a single page of the PDF
function renderPage(pdfDoc, pageNum, canvas) {
    pdfDoc.getPage(pageNum).then(page => {
        const context = canvas.getContext('2d');
        const viewport = page.getViewport({ scale: 1.5 });

        // Set canvas dimensions
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        // Render PDF page into canvas context
        const renderContext = {
            canvasContext: context,
            viewport: viewport
        };

        page.render(renderContext).promise.then(() => {
            console.log('Page rendered:', pageNum);
        }).catch(error => {
            console.error('Error rendering page:', error);
        });
    });
}

// Function to open the modal and display the file
function openModal(filePath, fileType) {
    console.log('openModal called with:', filePath, fileType);

    const modal = document.getElementById('fileModal');
    modal.style.display = 'block';

    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = ''; // Clear previous content

    // Check file type and render accordingly
    fileType = fileType.toLowerCase();
    if (fileType === 'pdf') {
        const pdfContainer = document.createElement('div');
        modalContent.appendChild(pdfContainer);
        renderPDF(filePath, pdfContainer);
    } else if (fileType.startsWith('image') || fileType === 'png' || fileType === 'jpg' || fileType === 'jpeg') {
        modalContent.innerHTML = `<img src="${filePath}" alt="File preview" style="max-width: 100%; max-height: 500px;" oncontextmenu="return false;" draggable="false">`;
    } else {
        modalContent.innerHTML = `<p>Unsupported file type for preview.</p>`;
    }
}

// Function to close the modal
function closeModal() {
    const modal = document.getElementById('fileModal');
    modal.style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('fileModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
};
