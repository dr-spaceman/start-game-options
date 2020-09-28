import React from 'react';
import classNames from 'classnames';

import questionblock from '../../images/icons/questionblock.png';

export function QuestionBlock({ className: classNameProp, ...props }) {
    const className = classNames(classNameProp, 'icon');
    return React.createElement('img', {
        ...props, src: questionblock, alt: '[?]', className,
    });
}

export default { QuestionBlock };
