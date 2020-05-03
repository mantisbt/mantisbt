import React from 'react';
import styled from 'styled-components';

type Props = {
  onInputChange: (value: string) => void;
  onEnterKeyDown?: () => void;
  renderItem: (item: any) => React.ReactNode;
  onSelectItem: (item: any) => void;

  options: Array<any>;
  value: string;
}


const DropdownTextInput = ({
  renderItem,
  onSelectItem,
  onInputChange,
  onEnterKeyDown,
  value,
  options,
}: Props) => {
  const [expanded, setExpanded] = React.useState<boolean>(false);
  const [index, setIndex] = React.useState<number>(-1);
  const DropdownRef = React.useRef<any>(null);
  React.useEffect(() => {
    setExpanded(!!options.length);
  }, [options]);

  React.useEffect(() => {
    function handleClickOutSide(event: MouseEvent) {
      if (DropdownRef.current && !DropdownRef.current!.contains(event.target)) {
        setExpanded(false);
      }
    }
    
    document.addEventListener('mousedown', handleClickOutSide);
    return () => {
      document.removeEventListener('mousedown', handleClickOutSide);
    }
  }, [DropdownRef]);

  React.useEffect(() => {
    function handleArrowKeyDown(event: KeyboardEvent) {
      if (expanded) {
        event.preventDefault();
        switch(event.key) {
          case 'ArrowDown':
            if (index < options.length - 1) {
              setIndex(index + 1);
              onSelectItem(options[index + 1]);
            }
            break;
          case 'ArrowUp':
            if (index > 0) {
              setIndex(index - 1);
              onSelectItem(options[index - 1]);
            } 
            break;
          default:
            break;
        }
      }
    }

    function handleMouseMoveOverDropdown(event: MouseEvent) {
      expanded && (index > -1) && DropdownRef.current && DropdownRef.current!.contains(event.target) && setIndex(-1);
    }

    document.addEventListener('keydown', handleArrowKeyDown);
    document.addEventListener('mousemove', handleMouseMoveOverDropdown);
    return () => {
      document.removeEventListener('keydown', handleArrowKeyDown);
      document.removeEventListener('mousemove', handleMouseMoveOverDropdown);
    }
  }, [expanded, index]);
  
  return (
    <Container>
      <input
        type='text'
        className='input-sm'
        onChange={(e) => onInputChange(e.target.value)}
        onKeyDown={(e) => e.key === 'Enter' && onEnterKeyDown && onEnterKeyDown()}
        onFocus={() => {
          options.length && setExpanded(!expanded);
        }}
        value={value}
      />
      <Dropdown className='tt-menu' open={expanded} ref={DropdownRef}>
        {options.map((option, _index) => (
          <div
            key={_index}
            className={`tt-suggestion tt-selectable${index === _index ? ' tt-cursor': ''}`}
            style={{ fontSize: 14 }}
            onClick={() => {
              setExpanded(false);
              onSelectItem(option);
            }}
          >
            {renderItem(option)}
          </div>
        ))}
      </Dropdown>
    </Container>
  )
}


const Container = styled.span`
  position: relative;
  display: inline-block;
  white-space: nowrap;
`;

const Dropdown = styled.div<{ open: boolean }>`
  position: absolute;
  top: 100%;
  left: 0px;
  z-index: 200;
  width: fit-content !important;
  display: ${props => props.open ? 'block' : 'none'}
`;

export default DropdownTextInput;
