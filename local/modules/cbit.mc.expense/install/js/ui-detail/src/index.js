import {UiDetail} from './scripts/ui-detail';
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.Expense.UiDetail = new UiDetail(Extension.getSettings('cbit.mc.expense.ui-detail'));
    }
    catch (e)
    {
        console.log('Expense UiDetail error', e)
    }
});