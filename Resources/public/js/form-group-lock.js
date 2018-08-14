(function () {
    "use strict";

    setTimeout(function () {
        main();
    }, 100);

    function main() {
        let formGroupLock = document.querySelectorAll('.form-group[data-lock]');

        formGroupLock.forEach(function (formGroup) {
            let formLabel = formGroup.getElementsByTagName('label')[0];
            let inputs = formGroup.querySelectorAll('.form-control, input[type=checkbox]');

            setDisabledOnInputs(inputs, true);

            let padlockCheckbox = createCheckbox(inputs[0].id);
            let padlockLabel = createLabel(padlockCheckbox);

            formLabel.appendChild(padlockCheckbox);
            formLabel.appendChild(padlockLabel);

            padlockCheckbox.addEventListener('change', function (event) {
                let padlockCheckbox = event.currentTarget;
                let status = padlockCheckbox.checked;

                setDisabledOnInputs(inputs, status);
            });
        });

        // Reset disabled on all fields before submit
        for (let i = 0; i < document.forms.length; i++) {
            document.forms[i].addEventListener('submit', function () {
                let formGroupLock = document.querySelectorAll('.form-group[data-lock]');

                formGroupLock.forEach(function (formGroup) {
                    let inputs = formGroup.querySelectorAll('.form-control, input[type=checkbox]');

                    setDisabledOnInputs(inputs, false);
                });
            });
        }
    }

    function setDisabledOnInputs(inputs, status)
    {
        inputs.forEach(function (input) {
            input.disabled = status;
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
