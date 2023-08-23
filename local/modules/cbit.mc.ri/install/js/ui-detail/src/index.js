import {UiDetail} from './scripts/ui-detail';
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.RI.UiDetail = new UiDetail(Extension.getSettings('cbit.mc.ri.ui-detail'));
    }
    catch (e)
    {
        console.log('R&I UiDetail error', e)
    }
});