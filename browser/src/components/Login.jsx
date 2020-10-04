import React from 'react';

import Modal from './Modal.jsx';
import { QuestionBlock } from '../lib/icons.js';
import { loadingIcon } from '../../images/icons/loading_mascot.gif';

const API_ENDPOINT = `${process.env.API_ENDPOINT}/login`;

const initialState = {
    isOpen: false,
    isLoading: false,
    isError: false,
    error: {},
};
const reducer = (state, action) => {
    switch (action.type) {
        case 'TOGGLE':
            return {
                ...state,
                isOpen: !state.isOpen,
            };
        case 'INIT':
            return {
                ...state,
                isLoading: true,
            };
        case 'LOGIN_SUCCESS':
            return {
                ...state,
                isLoading: false,
                isError: false,
            };
        case 'LOGIN_ERROR':
            return {
                ...state,
                isLoading: false,
                isError: true,
                error: action.error,
            };
        default:
            throw new Error();
    }
};

export default function Login(props) {
    const { username } = props;

    const form = React.useRef();

    const [state, dispatchState] = React.useReducer(reducer, initialState);

    const toggleOpen = (event) => {
        event.preventDefault();
        dispatchState({ type: 'TOGGLE' });
    };

    const handleSubmit = (event) => {
        event.preventDefault();

        dispatchState({ type: 'INIT' });

        const payload = {
            password: form.current.password.value,
        };
        const userInput = form.current.username.value;
        if (userInput.includes('@')) {
            payload.email = userInput;
        } else {
            payload.username = userInput;
        }

        fetch(API_ENDPOINT, {
            method: 'POST',
            mode: 'same-origin',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        }).then((response) => response.json())
            .then((result) => {
                console.log(result);
                if (result.collection.errors) {
                    dispatchState({ type: 'LOGIN_ERROR', error: result.collection.errors[0] });
                } else {
                    dispatchState({ type: 'LOGIN_SUCCESS' });
                }
            }).catch(() => dispatchState({ type: 'LOGIN_ERROR' }));
    };

    const userLink = username && (
        <span className="user">
            <a href={`~${username}`}>{username}</a>
        </span>
    );

    const loginButton = (
        <a href="/login.php" title="Login" onClick={toggleOpen} className="user user-unknown">
            <QuestionBlock className="user-avatar thumbnail" />
            <span className="user-username">Login</span>
        </a>
    );

    return (
        <div id="login">
            {userLink || loginButton}
            <Modal open={state.isOpen} close={toggleOpen} closeButton={false}>
                <form ref={form} onSubmit={handleSubmit}>
                    {state.isError && <div className="error">{state.error.message}</div>}
                    <input type="text" name="username" placeholder="Username or Email" ref={(input) => input && input.focus()} />
                    <input type="password" name="password" placeholder="Password" />
                    <div>
                        {state.isLoading && <img src={loadingIcon} className="loading" alt="loading" />}
                        <button type="submit" disabled={state.isLoading}>Login</button>
                        <button type="button" onClick={toggleOpen}>Cancel</button>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
