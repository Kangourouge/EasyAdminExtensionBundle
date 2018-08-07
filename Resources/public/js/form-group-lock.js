(function () {
    "use strict";

    setTimeout(function () {
        main();
    }, 100);

    function main() {
        let formGroupLock = document.querySelectorAll('.form-group[data-lock]');

        formGroupLock.forEach(function (formGroup) {
            let formLabel = formGroup.getElementsByTagName('label')[0];
            let formInputs = formGroup.querySelectorAll('.form-control, input[type=checkbox]');

            formInputs.forEach(function (formInput) {
                formInput.disabled = true;
            });

            let padlockCheckbox = createCheckbox(formInputs[0].id);
            let padlockLabel = createLabel(padlockCheckbox);

            formLabel.appendChild(padlockCheckbox);
            formLabel.appendChild(padlockLabel);

            padlockCheckbox.addEventListener('change', function (event) {
                let padlockCheckbox = event.currentTarget;
                let status = padlockCheckbox.checked;

                formInputs.forEach(function (formInput) {
                    formInput.disabled = status;
                });
            });
        });
    }

    function createCheckbox(id) {
        let checkbox = document.createElement('input');

        checkbox.type = 'checkbox';
        checkbox.className = 'padlock';
        checkbox.id = id + '_lock';
        checkbox.checked = true;

        return checkbox;
    }

    function createLabel(checkbox) {
        let label = document.createElement('label');

        label.htmlFor = checkbox.id;

        return label;
    }
})();
