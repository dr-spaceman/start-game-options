import React from 'react';
import { CSSTransition } from 'react-transition-group';

export default function Login(props) {
    const { username } = props;

    const form = React.useRef();
    const handleSubmit = (event) => {
        event.preventDefault();
        console.log(form.current.username.value, form.current.password.value);
    };

    const [open, setOpen] = React.useState(false);
    const handleOpen = () => {
        setOpen(true);
    };

    const loginButton = <button type="button" onClick={handleOpen}>Login</button>;

    return (
        <>
            {username || loginButton}
            <CSSTransition in={open} timeout={500} classNames="modal" unmountOnExit>
                <div className="modal-container">
                    <div className="modal-overlay" role="button" onClick={() => setOpen(false)} aria-hidden="true" aria-label="close" />
                    <div className="modal dark">
                        <form ref={form} onSubmit={handleSubmit}>
                            <input type="text" name="username" placeholder="Username" ref={(input) => input && input.focus()} />
                            <input type="password" name="password" placeholder="Password" />
                            <div>
                                <button type="submit">Login</button>
                                <button type="button" onClick={() => setOpen(false)}>Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </CSSTransition>
        </>
    );
}
