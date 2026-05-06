(function () {
    let selectId = 0;

    function optionLabel(option) {
        return option ? option.textContent.trim() : '';
    }

    function selectedOption(select) {
        return select.options[select.selectedIndex] || null;
    }

    function closeAll(except) {
        document.querySelectorAll('.searchable-select.is-open').forEach(function (wrapper) {
            if (wrapper !== except) {
                wrapper.classList.remove('is-open');
                const input = wrapper.querySelector('.searchable-select__search');
                if (input) {
                    input.setAttribute('aria-expanded', 'false');
                }
            }
        });
        document.querySelectorAll('.searchable-select__dropdown.is-open').forEach(function (dropdown) {
            if (!except || dropdown.dataset.owner !== except.dataset.searchableId) {
                dropdown.classList.remove('is-open');
            }
        });
    }

    function initSearchableSelect(select) {
        if (!select || select.dataset.searchableReady === 'true') {
            return;
        }

        select.dataset.searchableReady = 'true';
        select.classList.add('searchable-select-native');
        select.tabIndex = -1;

        const wrapper = document.createElement('div');
        wrapper.className = 'searchable-select';
        wrapper.dataset.searchableId = 'searchable-select-' + (++selectId);
        if (select.classList.contains('filter-select')) {
            wrapper.classList.add('searchable-select--filter');
        }
        if (select.style.width) {
            wrapper.style.width = select.style.width;
        }
        if (select.style.marginTop) {
            wrapper.style.marginTop = select.style.marginTop;
        }
        if (select.style.marginRight) {
            wrapper.style.marginRight = select.style.marginRight;
        }
        if (select.style.marginBottom) {
            wrapper.style.marginBottom = select.style.marginBottom;
        }
        if (select.style.marginLeft) {
            wrapper.style.marginLeft = select.style.marginLeft;
        }

        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'searchable-select__search';
        search.autocomplete = 'off';
        search.setAttribute('aria-haspopup', 'listbox');
        search.setAttribute('aria-expanded', 'false');

        const dropdown = document.createElement('div');
        dropdown.className = 'searchable-select__dropdown';
        dropdown.dataset.owner = wrapper.dataset.searchableId;

        const optionsList = document.createElement('div');
        optionsList.className = 'searchable-select__options';
        optionsList.setAttribute('role', 'listbox');

        dropdown.append(optionsList);
        wrapper.append(search);
        select.after(wrapper);
        document.body.append(dropdown);

        function currentLabel() {
            const selected = selectedOption(select);
            return optionLabel(selected) || '';
        }

        function updateSearchLabel() {
            search.value = currentLabel();
            search.placeholder = select.dataset.placeholder || 'Select option';
            wrapper.classList.toggle('is-disabled', select.disabled);
            search.disabled = select.disabled;
        }

        function renderOptions(queryText) {
            const query = (queryText ?? search.value).trim().toLowerCase();
            const fragment = document.createDocumentFragment();
            let matches = 0;

            Array.from(select.options).forEach(function (option) {
                const label = optionLabel(option);
                const searchText = (label + ' ' + option.value).toLowerCase();

                if (query && !searchText.includes(query)) {
                    return;
                }

                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'searchable-select__option';
                item.textContent = label || option.value;
                item.dataset.value = option.value;
                item.setAttribute('role', 'option');

                if (option.selected) {
                    item.classList.add('is-selected');
                    item.setAttribute('aria-selected', 'true');
                }

                item.addEventListener('click', function () {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    wrapper.classList.remove('is-open');
                    dropdown.classList.remove('is-open');
                    search.setAttribute('aria-expanded', 'false');
                    updateSearchLabel();
                    renderOptions();
                    search.focus();
                });

                fragment.append(item);
                matches += 1;
            });

            optionsList.innerHTML = '';

            if (!matches) {
                const empty = document.createElement('div');
                empty.className = 'searchable-select__empty';
                empty.textContent = 'No options found';
                optionsList.append(empty);
                return;
            }

            optionsList.append(fragment);

            if (wrapper.classList.contains('is-open')) {
                positionDropdown();
            }
        }

        function positionDropdown() {
            const rect = search.getBoundingClientRect();
            const gap = 4;
            const edgePadding = 8;
            const spaceBelow = window.innerHeight - rect.bottom - gap - edgePadding;
            const spaceAbove = rect.top - gap - edgePadding;
            const openAbove = spaceBelow < 220 && spaceAbove > spaceBelow;
            const availableSpace = Math.max(120, openAbove ? spaceAbove : spaceBelow);
            const maxOptionsHeight = Math.min(210, availableSpace - 18);

            optionsList.style.maxHeight = maxOptionsHeight + 'px';
            dropdown.style.left = rect.left + 'px';
            dropdown.style.width = rect.width + 'px';
            dropdown.style.right = 'auto';

            const dropdownHeight = dropdown.offsetHeight || maxOptionsHeight + 18;
            const top = openAbove
                ? Math.max(edgePadding, rect.top - gap - dropdownHeight)
                : Math.min(rect.bottom + gap, window.innerHeight - dropdownHeight - edgePadding);

            dropdown.style.top = top + 'px';
        }

        function openDropdown() {
            if (select.disabled) {
                return;
            }

            closeAll(wrapper);
            wrapper.classList.add('is-open');
            dropdown.classList.add('is-open');
            search.setAttribute('aria-expanded', 'true');
            search.select();
            renderOptions('');
            positionDropdown();
        }

        search.addEventListener('focus', openDropdown);
        search.addEventListener('click', openDropdown);
        search.addEventListener('input', function () {
            if (!wrapper.classList.contains('is-open')) {
                openDropdown();
            }
            renderOptions();
            positionDropdown();
        });
        search.addEventListener('blur', function () {
            window.setTimeout(function () {
                if (!wrapper.contains(document.activeElement) && !dropdown.contains(document.activeElement)) {
                    wrapper.classList.remove('is-open');
                    dropdown.classList.remove('is-open');
                    search.setAttribute('aria-expanded', 'false');
                    updateSearchLabel();
                }
            }, 120);
        });
        search.addEventListener('keydown', function (event) {
            if (!wrapper.classList.contains('is-open')) {
                openDropdown();
            }

            const visibleOptions = Array.from(optionsList.querySelectorAll('.searchable-select__option'));
            const currentIndex = visibleOptions.findIndex(function (option) {
                return option.classList.contains('is-active');
            });

            if (event.key === 'Escape') {
                wrapper.classList.remove('is-open');
                dropdown.classList.remove('is-open');
                search.setAttribute('aria-expanded', 'false');
                updateSearchLabel();
                search.blur();
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                const next = visibleOptions[Math.min(currentIndex + 1, visibleOptions.length - 1)];
                visibleOptions.forEach(function (option) { option.classList.remove('is-active'); });
                if (next) next.classList.add('is-active');
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                const next = visibleOptions[Math.max(currentIndex - 1, 0)];
                visibleOptions.forEach(function (option) { option.classList.remove('is-active'); });
                if (next) next.classList.add('is-active');
            }

            if (event.key === 'Enter') {
                const active = optionsList.querySelector('.searchable-select__option.is-active');
                if (active) {
                    event.preventDefault();
                    active.click();
                }
            }
        });

        select.addEventListener('change', function () {
            updateSearchLabel();
            renderOptions();
        });
        select.addEventListener('focus', openDropdown);

        new MutationObserver(function () {
            updateSearchLabel();
            renderOptions();
        }).observe(select, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['disabled', 'selected']
        });

        updateSearchLabel();
        renderOptions();

        window.addEventListener('scroll', function () {
            if (wrapper.classList.contains('is-open')) {
                positionDropdown();
            }
        }, true);
        window.addEventListener('resize', function () {
            if (wrapper.classList.contains('is-open')) {
                positionDropdown();
            }
        });
    }

    window.initSearchableSelects = function (root) {
        (root || document).querySelectorAll('select.searchable-select').forEach(initSearchableSelect);
    };

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.searchable-select') && !event.target.closest('.searchable-select__dropdown')) {
            closeAll();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        window.initSearchableSelects(document);
    });
})();
