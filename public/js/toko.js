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

        // Foto per warna: tampilkan foto warna terpilih (+ foto umum), foto utama = foto pertama warna itu.
        const galeri = document.querySelector('[data-galeri]');
        const gantiFoto = () => {
            if (!galeri) return;
            const warna = terpilih()[galeri.dataset.opsiWarna];
            const item = [...galeri.querySelectorAll('.galeri-thumb li')];
            const milikWarna = item.filter((li) => warna && li.dataset.warna === warna);
            item.forEach((li) => {
                li.hidden = milikWarna.length > 0 && li.dataset.warna !== undefined && li.dataset.warna !== warna;
            });
            const daftar = galeri.querySelector('.galeri-thumb');
            if (daftar) daftar.hidden = item.filter((li) => !li.hidden).length <= 1;
            const pertama = milikWarna[0]?.querySelector('[data-foto]');
            if (pertama) pertama.click();
        };

        radios.forEach((r) => r.addEventListener('change', () => {
            render();
            if (galeri && kunci(r) === galeri.dataset.opsiWarna) gantiFoto();
        }));
        render();
        gantiFoto();
    });
})();

// Chatbot: jawaban otomatis dari server, tombol WA/LINE kalau perlu admin.
(() => {
    const root = document.querySelector('[data-chat]');
    if (!root) return;

    const buka = root.querySelector('[data-chat-buka]');
    const panel = root.querySelector('.chat-panel');
    const isi = root.querySelector('[data-chat-isi]');
    const saran = root.querySelector('[data-chat-saran]');
    const form = root.querySelector('[data-chat-form]');
    const input = form.querySelector('input');
    const csrf = document.querySelector('form [name="_token"]')?.value;
    let dimulai = false;

    const tautkan = (teks) => {
        const frag = document.createDocumentFragment();
        teks.split(/(https?:\/\/\S+)/g).forEach((bagian, i) => {
            if (i % 2) {
                const a = document.createElement('a');
                a.href = bagian; a.textContent = bagian; a.target = '_blank'; a.rel = 'noopener';
                frag.append(a);
            } else {
                frag.append(bagian);
            }
        });
        return frag;
    };

    const pesan = (teks, dari, kontak = []) => {
        const el = document.createElement('div');
        el.className = `chat-pesan chat-${dari}`;
        const p = document.createElement('p');
        p.append(tautkan(teks));
        el.append(p);
        if (kontak.length) {
            const k = document.createElement('div');
            k.className = 'chat-kontak';
            kontak.forEach((c) => {
                const a = document.createElement('a');
                a.href = c.url; a.target = '_blank'; a.rel = 'noopener';
                a.className = `tombol tombol-kecil chat-${c.jenis}`;
                a.textContent = c.label;
                k.append(a);
            });
            el.append(k);
        }
        isi.append(el);
        isi.scrollTop = isi.scrollHeight;
    };

    const tampilkanSaran = (daftar) => {
        saran.replaceChildren(...daftar.map((t) => {
            const b = document.createElement('button');
            b.type = 'button'; b.className = 'chip-saran'; b.textContent = t;
            b.addEventListener('click', () => kirim(t));
            return b;
        }));
    };

    const minta = async (metode, badan) => {
        const res = await fetch(root.dataset.url, {
            method: metode,
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' },
            body: badan ? JSON.stringify(badan) : undefined,
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error(res.status);
        return res.json();
    };

    const mulai = async () => {
        if (dimulai) return;
        dimulai = true;
        try {
            const d = await minta('GET');
            pesan(d.teks, 'bot');
            tampilkanSaran(d.saran);
        } catch {
            pesan(root.dataset.tGalat, 'bot');
            dimulai = false;
        }
    };

    const kirim = async (teks) => {
        teks = teks.trim();
        if (!teks) return;
        pesan(teks, 'saya');
        input.value = '';
        try {
            const d = await minta('POST', { pesan: teks });
            pesan(d.teks, 'bot', d.kontak);
        } catch {
            pesan(root.dataset.tGalat, 'bot');
        }
    };

    const setBuka = (terbuka) => {
        panel.hidden = !terbuka;
        buka.setAttribute('aria-expanded', String(terbuka));
        root.classList.toggle('terbuka', terbuka);
        if (terbuka) { mulai(); input.focus(); } else { buka.focus(); }
    };

    buka.addEventListener('click', () => setBuka(panel.hidden));
    root.querySelector('[data-chat-tutup]').addEventListener('click', () => setBuka(false));
    panel.addEventListener('keydown', (e) => { if (e.key === 'Escape') setBuka(false); });
    form.addEventListener('submit', (e) => { e.preventDefault(); kirim(input.value); });
})();

// Pembayaran: salin nomor VA & cek status otomatis sampai pembayaran masuk.
(() => {
    document.querySelectorAll('[data-salin]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const isi = btn.parentElement.querySelector('[data-salin-isi]').textContent.trim();
            try { await navigator.clipboard.writeText(isi); btn.textContent = btn.dataset.tTersalin; } catch { /* abaikan */ }
        });
    });

    const blok = document.querySelector('[data-cek-status]');
    if (!blok) return;
    const awal = blok.dataset.statusAwal;
    let percobaan = 0;
    const cek = async () => {
        percobaan += 1;
        try {
            const res = await fetch(blok.dataset.cekStatus, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (res.ok && (await res.json()).status !== awal) { window.location.reload(); return; }
        } catch { /* coba lagi nanti */ }
        if (percobaan < 120) setTimeout(cek, percobaan < 30 ? 5000 : 15000);
    };
    setTimeout(cek, 5000);
})();
