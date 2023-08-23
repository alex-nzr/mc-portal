import {UiDetail} from './scripts/ui-detail';
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.Expense.Subscription = new UiDetail(Extension.getSettings('cbit.mc.subscription.ui-detail'));
    }
    catch (e)
    {
        console.log('Subscription UiDetail error', e)
    }
});