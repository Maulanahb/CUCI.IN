<script>
    // ========================================================
    // ALPINE POS TRANSACTION MANAGER
    // ========================================================
    function posTransactionManager() {
        return {
            searchQuery: '',
            items: @json(old('items', [['service_id' => ($services->first()?->id ?? ''), 'weight' => '']])),
            servicesList: @json($services),
            servicesMap: @json($services->keyBy('id')),
            paymentChoice: '{{ old('pay_now') ? (old('payment_amount') ? 'dp' : 'full') : 'unpaid' }}',
            paymentMethod: '{{ old('payment_method', 'Tunai') }}',
            dpAmount: '{{ old('payment_amount', '') }}',

            get filteredServices() {
                let list = this.servicesList;
                if (this.searchQuery && this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase();
                    list = list.filter(s => s.name.toLowerCase().includes(q));
                }
                return list;
            },

            selectServiceCard(service) {
                const existingIndex = this.items.findIndex(i => String(i.service_id) === String(service.id));
                if (existingIndex !== -1) {
                    let currentWeight = parseFloat(this.items[existingIndex].weight) || 0;
                    this.items[existingIndex].weight = (currentWeight + 1).toFixed(2);
                } else {
                    if (this.items.length === 1 && (!this.items[0].service_id || this.items[0].service_id === '') && (!this.items[0].weight || this.items[0].weight === '')) {
                        this.items[0].service_id = service.id;
                        this.items[0].weight = '1.00';
                    } else {
                        this.items.push({ service_id: service.id, weight: '1.00' });
                    }
                }
            },

            getItemInCart(serviceId) {
                return this.items.find(i => String(i.service_id) === String(serviceId));
            },

            addItem() {
                this.items.push({ service_id: '', weight: '' });
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                } else {
                    this.items[0] = { service_id: '', weight: '' };
                }
            },

            stepWeight(index, delta) {
                let current = parseFloat(this.items[index].weight) || 0;
                let updated = Math.max(0.1, current + delta);
                this.items[index].weight = updated.toFixed(2);
            },

            getService(id) {
                if (!id) return null;
                return this.servicesMap[id] || null;
            },

            getSubtotal(item) {
                if (!item || !item.service_id) return 0;
                let s = this.getService(item.service_id);
                let w = parseFloat(item.weight) || 0;
                if (!s || w <= 0) return 0;
                return w * parseFloat(s.price);
            },

            getTotalAmount() {
                return this.items.reduce((sum, item) => sum + this.getSubtotal(item), 0);
            },

            getTotalWeight() {
                return this.items.reduce((sum, item) => sum + (parseFloat(item.weight) || 0), 0);
            },

            getMaxDuration() {
                let max = 0;
                for (let item of this.items) {
                    let s = this.getService(item.service_id);
                    if (s && s.estimated_duration > max) {
                        max = s.estimated_duration;
                    }
                }
                return max;
            },

            formatDuration(hours) {
                if (!hours || hours <= 0) return '0 Jam';
                if (hours < 24) return hours + ' Jam';
                const days = Math.floor(hours / 24);
                const remHours = hours % 24;
                if (remHours === 0) return days + ' Hari';
                return days + ' Hari ' + remHours + ' Jam';
            },

            formatRupiah(val) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
            }
        };
    }

    // ========================================================
    // DATA DIREKTORI PELANGGAN & PREVIEW LIVE
    // ========================================================
    const customersDirectory = @json($customerDirectory);

    function getCustomerInitials(name) {
        if (!name) return 'PL';
        const parts = name.trim().split(/\s+/);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return name.substring(0, 2).toUpperCase();
    }

    function updateCustomerPreview(customerId) {
        const card = document.getElementById('customer-preview-card');
        const strId = customerId ? String(customerId) : '';

        if (!strId || !customersDirectory[strId]) {
            if (card) card.classList.add('hidden');
            return;
        }

        const cust = customersDirectory[strId];
        const nameEl = document.getElementById('preview-name');
        const phoneEl = document.getElementById('preview-phone');
        const addressEl = document.getElementById('preview-address');
        const avatarEl = document.getElementById('preview-avatar');

        if (nameEl) nameEl.textContent = cust.name;
        if (phoneEl) phoneEl.textContent = cust.phone;
        if (avatarEl) avatarEl.textContent = getCustomerInitials(cust.name);

        if (addressEl) {
            if (cust.address && cust.address.trim() !== '') {
                addressEl.textContent = cust.address;
                addressEl.classList.remove('italic', 'text-slate-400');
                addressEl.classList.add('text-slate-600');
            } else {
                addressEl.textContent = 'Alamat belum diisi';
                addressEl.classList.add('italic', 'text-slate-400');
                addressEl.classList.remove('text-slate-600');
            }
        }

        if (card) card.classList.remove('hidden');
    }

    function onCustomerSelectChange(val) {
        updateCustomerPreview(val);
    }

    function resetCustomerSelection() {
        const select = document.getElementById('customer_id');
        if (select) {
            select.value = '';
            updateCustomerPreview('');
            select.focus();
        }
    }

    // Inisialisasi preview saat halaman selesai dimuat jika ada customer terpilih
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('customer_id');
        if (select && select.value) {
            updateCustomerPreview(select.value);
        }
    });

    // ========================================================
    // MODAL QUICK CUSTOMER AJAX
    // ========================================================
    function openQuickCustomerModal() {
        const modal = document.getElementById('modal-quick-customer');
        if (modal) {
            modal.classList.remove('hidden');
            document.getElementById('quick-customer-errors').classList.add('hidden');
            document.getElementById('quick-customer-errors-list').innerHTML = '';
            document.getElementById('form-quick-customer').reset();
            setTimeout(() => {
                const nameInput = document.getElementById('quick_customer_name');
                if (nameInput) nameInput.focus();
            }, 100);
        }
    }

    function closeQuickCustomerModal() {
        const modal = document.getElementById('modal-quick-customer');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Shortcut Keyboard
    document.addEventListener('keydown', function(event) {
        // ESC untuk menutup modal
        if (event.key === 'Escape') {
            closeQuickCustomerModal();
            return;
        }

        // "/" untuk mencari layanan di katalog
        const activeEl = document.activeElement;
        const isInputField = activeEl && (
            activeEl.tagName === 'INPUT' ||
            activeEl.tagName === 'TEXTAREA' ||
            activeEl.tagName === 'SELECT' ||
            activeEl.isContentEditable
        );

        const modal = document.getElementById('modal-quick-customer');
        const isModalOpen = modal && !modal.classList.contains('hidden');

        if (event.key === '/' && !isInputField && !isModalOpen) {
            event.preventDefault();
            const searchInput = document.getElementById('service-search-input');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });

    async function submitQuickCustomer(event) {
        event.preventDefault();
        const btn = document.getElementById('btn-save-quick-customer');
        const spinner = document.getElementById('btn-save-quick-customer-spinner');
        const btnText = document.getElementById('btn-save-quick-customer-text');
        const errorBox = document.getElementById('quick-customer-errors');
        const errorList = document.getElementById('quick-customer-errors-list');

        const name = document.getElementById('quick_customer_name').value.trim();
        const phone = document.getElementById('quick_customer_phone').value.trim();
        const address = document.getElementById('quick_customer_address').value.trim();

        if (!name || !phone) {
            return;
        }

        btn.disabled = true;
        spinner.classList.remove('hidden');
        btnText.textContent = 'Menyimpan...';
        errorBox.classList.add('hidden');
        errorList.innerHTML = '';

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}';

            const response = await fetch('{{ route("{$currentRoutePrefix}.customers.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    name: name,
                    phone: phone,
                    address: address,
                }),
            });

            const data = await response.json();

            if (response.ok && data.success && data.customer) {
                const cust = data.customer;
                const strId = String(cust.id);
                customersDirectory[strId] = cust;

                const customerSelect = document.getElementById('customer_id');
                if (customerSelect) {
                    const option = document.createElement('option');
                    option.value = cust.id;
                    option.textContent = `${cust.name} (${cust.phone})`;
                    option.selected = true;
                    customerSelect.appendChild(option);
                    customerSelect.value = cust.id;
                    updateCustomerPreview(strId);
                }

                closeQuickCustomerModal();
            } else if (response.status === 422 && data.errors) {
                errorList.innerHTML = '';
                Object.values(data.errors).forEach(errArray => {
                    errArray.forEach(err => {
                        const li = document.createElement('li');
                        li.textContent = err;
                        errorList.appendChild(li);
                    });
                });
                errorBox.classList.remove('hidden');
            } else {
                errorList.innerHTML = `<li>${data.message || 'Gagal menyimpan data pelanggan.'}</li>`;
                errorBox.classList.remove('hidden');
            }
        } catch (err) {
            errorList.innerHTML = '<li>Terjadi kesalahan koneksi. Silakan coba lagi.</li>';
            errorBox.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            spinner.classList.add('hidden');
            btnText.textContent = 'Simpan & Pilih';
        }
    }
</script>
