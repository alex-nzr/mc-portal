import {UiDetail} from './scripts/ui-detail';
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.Partner.UiDetail = new UiDetail(Extension.getSettings('cbit.mc.partner.ui-detail'));
    }
    catch (e)
    {
        console.log('Partner UiDetail error', e)
    }
});