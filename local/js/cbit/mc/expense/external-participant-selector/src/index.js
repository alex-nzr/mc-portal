import {ExternalParticipant} from "./scripts/external-participant";
import './styles/style.css';
import {Extension} from 'main.core';

BX.ready(() => {
    try
    {
        BX.Cbit.Mc.Expense.ExternalParticipant = new ExternalParticipant(
            Extension.getSettings('cbit.mc.expense.external-participant-selector')
        );
    }
    catch (e)
    {
        console.log('Expense ExternalParticipant error', e)
    }
});