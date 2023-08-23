import {FileInput} from "./scripts/file-input";
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.Core.FileInput = new FileInput(
            Extension.getSettings('cbit.mc.expense.file-input')
        );
    }
    catch (e)
    {
        console.log('Extension FileInput error', e)
    }
});