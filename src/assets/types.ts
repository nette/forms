export type FormElement = (HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement | HTMLButtonElement) & { form: HTMLFormElement };

// number can only be created by the validator
export type FormElementValue = string | string[] | boolean | FileList | number | null;

export type Validator = (
	elem: FormElement,
	arg: unknown,
	value: unknown,
	newValue: { value: unknown },
) => boolean | null;

export type Rule = {
	op: string;
	neg?: boolean;
	msg: string;
	arg?: unknown;
	rules?: Rule[];
	condition?: boolean;
	control?: string;
	toggle?: Record<string, boolean>;
};

export type FormError = {
	element: FormElement;
	message: string;
};

export type ToggleState = {
	elem: FormElement;
	state: boolean;
};
