document.addEventListener('DOMContentLoaded', () => {
    if (!protectAdminPage()) {
        return;
    }

    setupAdminUI();

    const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Perbarui isi artikel di sini...'
    });

    const isiInput = document.getElementById('isi');
    const fileInput = document.getElementById('gambar');
    const fileName = document.getElementById('fileName');

    quill.root.innerHTML = isiInput.value || '';

    fileInput.addEventListener('change', () => {
        fileName.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : 'Gunakan gambar lama jika tidak diganti';
    });

    document.getElementById('articleForm').addEventListener('submit', (event) => {
        const plainText = quill.getText().trim();

        if (!plainText) {
            event.preventDefault();
            alert('Isi artikel tidak boleh kosong.');
            return;
        }

        isiInput.value = quill.root.innerHTML;
    });
});