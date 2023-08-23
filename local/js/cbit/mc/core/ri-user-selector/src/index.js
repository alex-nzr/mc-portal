import {RIUserSelector} from './scripts/ri-user-selector';
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.Core.EntitySelector.RIUserSelector = new RIUserSelector(Extension.getSettings('cbit.mc.core.ri-user-selector'));
    }
    catch (e)
    {
        console.log('RIUserSelector extension error - ', e)
    }
});