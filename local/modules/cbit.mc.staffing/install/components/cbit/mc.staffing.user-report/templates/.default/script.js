BX.ready(function (){
    BX.namespace("Cbit.Mc.Staffing.UserReportComponent");

    BX.Cbit.Mc.Staffing.UserReportComponent = {
        init(params){
            this.signedParameters    = params.signedParameters;
            this.componentName       = params.componentName;
            this.userId              = params.userId;
            this.lastLoadedProjectId = 0;

            this.initProjectsMoreBtn();
        },

        initProjectsMoreBtn() {
            const btn = BX('user-report-block-projects-more');
            btn && btn.addEventListener('click', () => {
                btn.classList.add("ui-btn-wait");
                BX.ajax.runComponentAction(this.componentName, "getMoreProjects", {
                    signedParameters: this.signedParameters,
                    mode: 'ajax',
                    data: {
                        lastId: this.lastLoadedProjectId
                    }
                }).then( response => {
                    if (response.status === 'success')
                    {
                        if (typeof response?.data?.projects === 'object')
                        {
                            const tableBody = BX('user-report-block-project-table-body');
                            for (const id in response.data.projects)
                            {
                                if (tableBody && response.data.projects.hasOwnProperty(id))
                                {
                                    const row = BX.create({
                                        tag: 'tr',
                                        children: [
                                            BX.create({
                                                tag:'td',
                                                props:{
                                                    className: 'staffing-only'
                                                },
                                                text: response.data.projects[id]['PROJECT_CLIENT']
                                            }),
                                            BX.create({
                                                tag:'td',
                                                props:{
                                                    className: 'staffing-only'
                                                },
                                                text: response.data.projects[id]['PROJECT_DESCRIPTION']
                                            }),
                                            BX.create({
                                                tag:'td',
                                                text: response.data.projects[id]['PROJECT_NAME']
                                            }),
                                            BX.create({
                                                tag:'td',
                                                text: response.data.projects[id]['USER_ROLE']
                                            }),
                                            BX.create({
                                                tag:'td',
                                                text: response.data.projects[id]['INDUSTRY_NAME']
                                            }),
                                            BX.create({
                                                tag:'td',
                                                text: response.data.projects[id]['WORK_DATE_START']
                                            }),
                                            BX.create({
                                                tag:'td',
                                                text: response.data.projects[id]['WORK_DATE_FINISH']
                                            }),
                                            BX.create({
                                                tag:'td',
                                                text: response.data.projects[id]['WEEKS_IN_PROJECT']
                                            }),
                                            BX.create({
                                                tag:'td',
                                                text: response.data.projects[id]['PROJECT_ED']
                                            }),
                                        ]
                                    });
                                    tableBody.append(row);
                                }
                            }
                        }

                        this.lastLoadedProjectId = response?.data?.lastId
                        if(response?.data?.isFinal === 'Y')
                        {
                            btn.setAttribute('disabled', 'true');
                        }
                        btn.classList.remove("ui-btn-wait");
                    }
                    else
                    {
                        throw new Error('Something went wrong. Unknown response status - '.response.status);
                    }
                }).catch(response => {
                    const error = response.errors?.[0]?.message ?? 'Something went wrong. Unknown error';
                    this.showErrorPopup(error);
                    btn.classList.remove("ui-btn-wait");
                });
            });
        },

        showErrorPopup: function(error) {
            if (!error)
            {
                error = 'Operation ERROR';
            }

            BX.UI.Dialogs.MessageBox.show(
                {
                    message: `<div style="color: red;text-align: center;">${error}</div>`,
                    modal: true,
                    buttons: BX.UI.Dialogs.MessageBoxButtons.CANCEL,
                    cancelCaption: 'Got it',
                    onCancel: (messageBox) => {
                        messageBox.close();
                    },
                }
            );
        },
    }
})