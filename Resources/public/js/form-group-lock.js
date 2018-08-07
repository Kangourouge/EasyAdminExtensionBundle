(function(){
    "use strict";
    let formGroupLock = document.querySelectorAll('.form-group[data-lock]');

    formGroupLock.forEach(function(formGroup) {
        let formLabel = formGroup.getElementsByTagName('label')[0];
        let formInput = formGroup.getElementsByClassName('form-control')[0];
        let padlockCheckbox = createCheckbox(formInput.id);
        let padlockLabel = createLabel(padlockCheckbox);

        formInput.disabled = true;
        formLabel.appendChild(padlockCheckbox);
        formLabel.appendChild(padlockLabel);

        padlockCheckbox.addEventListener('change', function(event) {
            let padlockCheckbox = event.currentTarget;
            let target = padlockCheckbox.dataset.target;

            document.getElementById(target).disabled = padlockCheckbox.checked;
        });
    });

    function createCheckbox(id)
    {
        let checkbox = document.createElement('input');

        checkbox.type = 'checkbox';
        checkbox.className = 'padlock';
        checkbox.id = id + '_lock';
        checkbox.dataset.target = id;
        checkbox.checked = true;

        return checkbox;
    }

    function createLabel(checkbox)
    {
        let label = document.createElement('label');

        label.htmlFor = checkbox.id;

        return label;
    }
})();
