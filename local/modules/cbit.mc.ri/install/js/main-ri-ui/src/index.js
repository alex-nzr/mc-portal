import {MainRiUI} from './scripts/main-ri-ui';
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.RI.MainRiUI = new MainRiUI(Extension.getSettings('cbit.mc.ri.main-ri-ui'));
    }
    catch (e)
    {
        console.log('MainRiUI extension error - ', e)
    }
});