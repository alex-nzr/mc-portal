import {MainUI} from './scripts/main-ui';
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.Profile.MainUI = new MainUI(Extension.getSettings('cbit.mc.profile.main-ui'));
    }
    catch (e)
    {
        console.log('MainUI extension error - ', e)
    }
});