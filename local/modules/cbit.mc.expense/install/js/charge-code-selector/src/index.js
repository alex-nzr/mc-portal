import {ChargeCodeSelector} from "./scripts/charge-code-selector";
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.Expense.ChargeCodeSelector = new ChargeCodeSelector(
            Extension.getSettings('cbit.mc.expense.charge-code-selector')
        );
    }
    catch (e)
    {
        console.log('Expense ChargeCodeSelector error', e)
    }
});