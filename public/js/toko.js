// Storefront: interaksi kecil tanpa framework.
(() => {
    // Ganti bahasa / mata uang / urutan langsung saat dipilih.
    document.querySelectorAll('form[data-auto-submit] select, select[data-auto-submit-field]').forEach((el) => {
        el.addEventListener('change', () => el.form.submit());
    });

    // Galeri foto.
    document.querySelectorAll('[data-galeri]').forEach((galeri) => {
        const utama = galeri.querySelector('[data-galeri-utama]');
        galeri.querySelectorAll('[data-foto]').forEach((btn) => {
            btn.addEventListener('click', () => {
                utama.src = btn.dataset.foto;
                galeri.querySelectorAll('[data-foto]').forEach((b) => b.removeAttribute('aria-current'));
                btn.setAttribute('aria-current', 'true');
            });
        });
    });

    // Pemilih varian: cocokkan opsi terpilih ke varian, update harga/stok/SKU.
    document.querySelectorAll('[data-pemilih]').forEach((root) => {
        const varian = JSON.parse(root.querySelector('[data-varian]').textContent);
        const harga = root.querySelector('[data-harga]');
        const stok = root.querySelector('[data-stok]');
        const sku = root.querySelector('[data-sku]');
        const beli = root.querySelector('[data-tombol-beli]');
        const radios = [...root.querySelectorAll('input[type="radio"][name^="opsi["]')];
        const kunci = (r) => r.name.slice(5, -1);

        const terpilih = () => {
            const pilih = {};
            radios.filter((r) => r.checked).forEach((r) => { pilih[kunci(r)] = r.value; });
            return pilih;
        };
        const cocok = (v, pilih) => Object.entries(pilih).every(([k, n]) => v.opsi[k] === n);

        const render = () => {
            const pilih = terpilih();
            const v = varian.find((x) => cocok(x, pilih));

            root.querySelectorAll('[data-terpilih]').forEach((el) => {
                const r = radios.find((x) => x.checked && kunci(x) === el.dataset.terpilih);
                el.textContent = r ? r.dataset.label : '';
            });

            // Tandai pilihan yang habis, dengan opsi lain tetap seperti yang dipilih.
            radios.forEach((r) => {
                const coba = { ...pilih, [kunci(r)]: r.value };
                const ada = varian.some((x) => cocok(x, coba) && x.stok > 0);
                r.closest('.chip').classList.toggle('kosong-stok', !ada);
            });

            stok.classList.remove('habis');
            if (beli) beli.disabled = !v || v.stok <= 0;
            if (!v) {
                stok.textContent = stok.dataset.tTidakAda;
                stok.classList.add('habis');
                sku.textContent = '';
                return;
            }
            harga.textContent = v.harga;
            sku.textContent = v.sku;
            if (v.stok <= 0) {
                stok.textContent = stok.dataset.tHabis;
                stok.classList.add('habis');
            } else if (v.stok <= 5) {
                stok.textContent = stok.dataset.tSisa.replace(':count', v.stok);
            } else {
                stok.textContent = stok.dataset.tAda;
            }
        };

        radios.forEach((r) => r.addEventListener('change', render));
        render();
    });
})();
