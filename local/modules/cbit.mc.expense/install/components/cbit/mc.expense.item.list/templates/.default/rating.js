BX.ready(function()
{
    const ratingForm  = BX('user-rating-csv-form');
    const ratingInput = BX('user-rating-csv-input');
    const ratingText  = BX('user-rating-upload-text');
    if (ratingForm && ratingInput && ratingText)
    {
        ratingInput.addEventListener('change', (e) => {
            ratingText.textContent = 'Loading...';
            ratingForm.style.pointerEvents = 'none';
            const formData = new FormData(ratingForm);

            BX.ajax.runAction('cbit.mc:expense.base.updateUsersRating', {
                sessid: BX.bitrix_sessid(),
                data: formData
            }).then( response => {
                if (response.status === 'success')
                {
                    BX.Cbit?.Mc?.Core?.MainUI?.showSuccessPopup();
                    ratingText.textContent = 'Upload rating';
                    ratingForm.style.pointerEvents = '';
                    ratingInput.value = '';
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status - '.response.status);
                }
            }).catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                BX.Cbit?.Mc?.Core?.MainUI?.showErrorPopup(error);
                ratingText.textContent = 'Upload rating';
                ratingForm.style.pointerEvents = '';
                ratingInput.value = '';
            });
        });
    }
});