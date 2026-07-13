// Lokasi: assets/js/script.js
// Interaksi sisi klien: toggle navbar, validasi form, konfirmasi hapus

document.addEventListener('DOMContentLoaded', function () {
    // Toggle navbar mobile
    var toggle = document.getElementById('navToggle');
    var links = document.getElementById('navLinks');
    if (toggle && links) {
        toggle.addEventListener('click', function () {
            links.classList.toggle('show');
        });
    }

    // Konfirmasi hapus buku
    document.querySelectorAll('.confirm-delete').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var ok = confirm('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.');
            if (!ok) {
                e.preventDefault();
            }
        });
    });

    // Validasi form buku (tambah / edit)
    var bookForm = document.getElementById('bookForm');
    if (bookForm) {
        bookForm.addEventListener('submit', function (e) {
            var title = bookForm.querySelector('[name="title"]');
            var author = bookForm.querySelector('[name="author"]');
            var description = bookForm.querySelector('[name="description"]');
            var price = bookForm.querySelector('[name="price"]');
            var errors = [];

            if (!title.value.trim()) errors.push('Judul wajib diisi.');
            if (!author.value.trim()) errors.push('Penulis wajib diisi.');
            if (!description.value.trim()) errors.push('Deskripsi wajib diisi.');

            var priceVal = parseFloat(price.value);
            if (isNaN(priceVal) || priceVal <= 0) {
                errors.push('Harga harus berupa angka positif.');
            }

            var coverInput = bookForm.querySelector('[name="cover_image"]');
            if (coverInput && coverInput.files.length > 0) {
                var coverExt = coverInput.files[0].name.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png'].indexOf(coverExt) === -1) {
                    errors.push('Cover hanya boleh JPG, JPEG, atau PNG.');
                }
            }

            var fileInput = bookForm.querySelector('[name="book_file"]');
            if (fileInput && fileInput.files.length > 0) {
                var fileExt = fileInput.files[0].name.split('.').pop().toLowerCase();
                if (fileExt !== 'pdf') {
                    errors.push('File buku harus berformat PDF.');
                }
            }

            var errorBox = document.getElementById('jsErrorBox');
            if (errors.length > 0) {
                e.preventDefault();
                if (errorBox) {
                    errorBox.innerHTML = errors.map(function (msg) {
                        return '<div>' + msg + '</div>';
                    }).join('');
                    errorBox.style.display = 'block';
                } else {
                    alert(errors.join('\n'));
                }
            }
        });
    }

    // Preview cover image sebelum upload
    var coverPreviewInput = document.querySelector('[name="cover_image"]');
    var coverPreviewImg = document.getElementById('coverPreview');
    if (coverPreviewInput && coverPreviewImg) {
        coverPreviewInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    coverPreviewImg.src = e.target.result;
                    coverPreviewImg.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
