document.addEventListener('DOMContentLoaded', () => {
    if (!protectAdminPage()) return;
    setupAdminUI();

    window.quillInstance = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Tulis isi artikel di sini...'
    });

    const isiInput = document.getElementById('isi');
    const fileInput = document.getElementById('gambar');
    const fileName = document.getElementById('fileName');

    fileInput.addEventListener('change', () => {
        fileName.textContent = fileInput.files.length > 0
            ? fileInput.files[0].name
            : 'Belum ada file dipilih';
    });

    document.getElementById('articleForm').addEventListener('submit', (event) => {
        const plainText = window.quillInstance.getText().trim();

        if (!plainText) {
            event.preventDefault();
            alert('Isi artikel tidak boleh kosong.');
            return;
        }

        isiInput.value = window.quillInstance.root.innerHTML;
    });
});