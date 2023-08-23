'use strict'

BX.ready(function () {
    const Approver = BX.namespace("Cbit.Mc.Profile.PhotoApproveComponent");

    Approver.init = function (params) {
        this.oldFileId = params.OLD_FILE_ID;
        this.newFileId = params.NEW_FILE_ID;
        this.userId    = params.USER_ID;

        this.approveBtn = BX('photo-approver-approve-btn');
        this.declineBtn = BX('photo-approver-decline-btn');
        if (this.approveBtn && this.declineBtn)
        {
            this.approveBtn.addEventListener('click', this.approveNewPhoto)
            this.declineBtn.addEventListener('click', this.declineNewPhoto)
        }
    }

    Approver.approveNewPhoto = function () {
        Approver.approveBtn.classList.add('ui-btn-wait');
        Approver.declineBtn.classList.add('btn-wait');

        const postData = {
            userId:    Approver.userId,
            newFileId: Approver.newFileId
        };

        /*if (Approver.oldFileId)
        {
            postData.oldFileId = Approver.oldFileId;
        }*/

        BX.ajax.runComponentAction('cbit:mc.profile.photo-approver.detail', 'approve', {
            mode: 'ajax',
            data: postData
        })
            .then(response => {
                if (response.status === 'success')
                {
                    Approver.showSuccessPopup();
                }
                else
                {
                    throw new Error('Something went wrong. Unknown response status.');
                }

                Approver.approveBtn.classList.remove('ui-btn-wait');
                Approver.declineBtn.classList.remove('btn-wait');
            })
            .catch(response => {
                const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                Approver.showErrorPopup(error);

                Approver.approveBtn.classList.remove('ui-btn-wait');
                Approver.declineBtn.classList.remove('btn-wait');
            });
    };

    Approver.declineNewPhoto = function () {

        BX.UI.Dialogs.MessageBox.show(
            {
                message: `<h3>${BX.message("WRITE_REASON")}</h3>
                          <div class="ui-ctl ui-ctl-textbox ui-ctl-block">
                            <textarea id="photo-approver-decline-reason" class="ui-ctl-element"></textarea>
                          </div>`,
                modal: true,
                buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
                onOk: (messageBox) => {
                    const textarea = BX('photo-approver-decline-reason');
                    const reason   = textarea ? textarea.value : BX.message("REASON_NOT_WROTE")

                    const postData = {
                        userId:    Approver.userId,
                        newFileId: Approver.newFileId,
                        reason:    reason
                    };

                    BX.ajax.runComponentAction('cbit:mc.profile.photo-approver.detail', 'decline', {
                        mode: 'ajax',
                        data: postData
                    }).then(response => {
                            if (response.status === 'success')
                            {
                                messageBox.close();
                                Approver.showSuccessPopup();
                            }
                            else
                            {
                                throw new Error('Something went wrong. Unknown response status.');
                            }
                        })
                        .catch(response => {
                            const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                            Approver.showErrorPopup(error);
                        });
                }
            }
        );
    };

    Approver.showSuccessPopup = function (){
        BX.UI.Dialogs.MessageBox.alert( BX.message("OPERATION_SUCCESSFUL"), () => {
            BX.SidePanel.Instance.close();
        });
    }

    Approver.showErrorPopup = function (error){
        BX.UI.Dialogs.MessageBox.show(
            {
                message: error,
                modal: true,
                buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
                onOk: function(messageBox)
                {
                    messageBox.close();
                }
            }
        );
    }
})